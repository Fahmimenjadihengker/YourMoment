<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\WalletSetting;
use App\Models\Category;
use App\Services\FinancialSummaryService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\SpendingPatternAnalyzer;

class AIRecommendationService
{
    /**
     * Standar ideal persentase pengeluaran per kategori
     */
    protected array $idealPercentages = [
        'Makan' => ['min' => 40, 'max' => 50],
        'Transport' => ['min' => 10, 'max' => 20],
        'Nongkrong' => ['min' => 10, 'max' => 20],
        'Akademik' => ['min' => 5, 'max' => 10],
        'Lainnya' => ['min' => 0, 'max' => 10],
    ];

    /**
     * Generate rekomendasi AI berdasarkan data keuangan user
     */
    public function generateRecommendation(int $userId): array
    {
        // Ambil wallet settings (atau buat default jika belum ada)
        $walletSetting = WalletSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'balance' => 0,
                'monthly_allowance' => null,
                'weekly_allowance' => null,
                'financial_goal' => null,
                'notes' => null,
            ]
        );

        // Hitung periode 7 hari terakhir
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(7);

        // Ambil total pengeluaran 7 hari terakhir
        $totalExpense = $this->getTotalExpense($userId, $startDate, $endDate);

        // Ambil semua transaksi expense (untuk analisis notes)
        $expenseTransactions = $this->getExpenseTransactions($userId, $startDate, $endDate);

        // Ambil breakdown per kategori
        $categoryBreakdown = $this->getCategoryBreakdown($userId, $startDate, $endDate);

        // Hitung persentase per kategori
        $categoryPercentages = $this->calculatePercentages($categoryBreakdown, $totalExpense);

        // Analisis dan generate rekomendasi
        $analysis = $this->analyzeSpending($categoryPercentages, $walletSetting);

        // Generate teks rekomendasi (pass transactions untuk pattern analysis)
        $recommendation = $this->generateRecommendationText($analysis, $walletSetting, $totalExpense, $expenseTransactions);

        return [
            'wallet_setting' => $walletSetting,
            'total_expense_7_days' => $totalExpense,
            'category_breakdown' => $categoryBreakdown,
            'category_percentages' => $categoryPercentages,
            'analysis' => $analysis,
            'recommendation' => $recommendation,
            'period' => [
                'start' => $startDate->format('d M Y'),
                'end' => $endDate->format('d M Y'),
            ],
        ];
    }

    /**
     * Ambil total pengeluaran dalam periode tertentu
     */
    protected function getTotalExpense(int $userId, Carbon $startDate, Carbon $endDate): float
    {
        return (float) (Transaction::forUser($userId)
            ->expense()
            ->dateRange($startDate, $endDate)
            ->sum('amount') ?? 0);
    }

    /**
     * Ambil breakdown pengeluaran per kategori
     */
    protected function getCategoryBreakdown(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        $transactions = Transaction::forUser($userId)
            ->expense()
            ->dateRange($startDate, $endDate)
            ->with('category')
            ->get();
        
        // Defensive: return empty collection if no transactions
        if ($transactions->isEmpty()) {
            return collect([]);
        }
        
        return $transactions
            ->groupBy(fn($t) => $t->category?->name ?? 'Tanpa Kategori')
            ->map(function ($transactions, $categoryName) {
                $category = $transactions->first()->category;
                return [
                    'name' => $categoryName,
                    'icon' => $category->icon ?? '📌',
                    'color' => $category->color ?? '#6b7280',
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                    'transactions' => $transactions, // Include transactions for note analysis
                ];
            });
    }

    /**
     * Ambil semua transaksi expense dalam periode
     */
    protected function getExpenseTransactions(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::forUser($userId)
            ->expense()
            ->dateRange($startDate, $endDate)
            ->with('category')
            ->get();
    }

    /**
     * Hitung persentase pengeluaran per kategori
     */
    protected function calculatePercentages(Collection $breakdown, float $totalExpense): Collection
    {
        if ($totalExpense <= 0) {
            return collect();
        }

        return $breakdown->map(function ($category) use ($totalExpense) {
            $percentage = ($category['total'] / $totalExpense) * 100;
            return array_merge($category, [
                'percentage' => round($percentage, 1),
            ]);
        })->sortByDesc('percentage');
    }

    /**
     * Analisis spending dibandingkan dengan standar ideal
     */
    protected function analyzeSpending(Collection $percentages, ?WalletSetting $walletSetting): array
    {
        $analysis = [
            'overspending' => [],
            'underspending' => [],
            'on_track' => [],
            'insights' => [],
        ];

        foreach ($percentages as $categoryName => $data) {
            $ideal = $this->idealPercentages[$categoryName] ?? ['min' => 0, 'max' => 15];
            $percentage = $data['percentage'];

            if ($percentage > $ideal['max']) {
                $analysis['overspending'][] = [
                    'category' => $categoryName,
                    'icon' => $data['icon'],
                    'percentage' => $percentage,
                    'ideal_max' => $ideal['max'],
                    'excess' => round($percentage - $ideal['max'], 1),
                ];
            } elseif ($percentage < $ideal['min']) {
                $analysis['underspending'][] = [
                    'category' => $categoryName,
                    'icon' => $data['icon'],
                    'percentage' => $percentage,
                    'ideal_min' => $ideal['min'],
                ];
            } else {
                $analysis['on_track'][] = [
                    'category' => $categoryName,
                    'icon' => $data['icon'],
                    'percentage' => $percentage,
                ];
            }
        }

        // Tambahan insight berdasarkan wallet setting
        if ($walletSetting) {
            if ($walletSetting->financial_goal > 0) {
                $analysis['has_goal'] = true;
                $analysis['goal_amount'] = $walletSetting->financial_goal;
            }

            if ($walletSetting->weekly_allowance > 0) {
                $analysis['has_weekly_budget'] = true;
                $analysis['weekly_budget'] = $walletSetting->weekly_allowance;
            }
        }

        return $analysis;
    }

    /**
     * Generate teks rekomendasi yang santai dan supportive
     */
    protected function generateRecommendationText(array $analysis, ?WalletSetting $walletSetting, float $totalExpense, ?Collection $transactions = null): string
    {
        $recommendations = [];

        // Jika tidak ada pengeluaran
        if ($totalExpense <= 0) {
            return $this->getNoExpenseMessage();
        }

        // Opening yang personal
        $recommendations[] = $this->getOpeningMessage($totalExpense);

        // Analyze spending patterns untuk rekomendasi kontekstual
        $analyzer = new SpendingPatternAnalyzer(
            $totalExpense,
            $walletSetting->monthly_allowance ?? $walletSetting->weekly_allowance ?? 0,
            $walletSetting->monthly_allowance ? 'monthly' : 'weekly',
            $this->categoryBreakdown ?? collect(),
            $walletSetting->balance ?? 0,
            $walletSetting->financial_goal ?? 0,
            $transactions // Pass transactions for note analysis
        );

        $patterns = $analyzer->analyze();
        
        // Generate pattern-based recommendations
        $patternRecommendations = $this->generatePatternBasedRecommendations($analyzer, $analysis);
        $recommendations = array_merge($recommendations, $patternRecommendations);

        // Savings goal message
        if ($walletSetting && $walletSetting->financial_goal > 0) {
            $recommendations[] = $this->getSavingsGoalMessage($walletSetting->financial_goal, $walletSetting->balance ?? 0);
        }

        // Closing yang motivating
        $recommendations[] = $this->getClosingMessage();

        return implode("\n\n", array_filter($recommendations));
    }

    /**
     * Generate pattern-based recommendations
     */
    private function generatePatternBasedRecommendations(SpendingPatternAnalyzer $analyzer, array $analysis): array
    {
        $recommendations = [];

        // High food spending patterns
        if ($analyzer->hasPattern('highFoodSpending')) {
            if ($analyzer->hasPattern('frequentOnlineFood')) {
                $recommendations[] = $this->getFrequentOnlineFoodMessage();
            } else {
                $recommendations[] = $this->getHighFoodSpendingMessage();
            }
        } elseif ($analyzer->hasPattern('moderateFoodSpending')) {
            $recommendations[] = $this->getBalancedFoodSpendingMessage();
        }

        // Hangout patterns
        if ($analyzer->hasPattern('highHangout')) {
            $recommendations[] = $this->getHighHangoutMessage();
        } elseif ($analyzer->hasPattern('moderateHangout')) {
            $recommendations[] = $this->getBalancedHangoutMessage();
        }

        // Transport patterns
        if ($analyzer->hasPattern('heavyTransport')) {
            $recommendations[] = $this->getHeavyTransportMessage();
        } elseif ($analyzer->hasPattern('moderateTransport')) {
            $recommendations[] = $this->getBalancedTransportMessage();
        }

        // Budget overspending
        if ($analyzer->hasPattern('isOverspentWeekly') || $analyzer->hasPattern('isOverspentMonthly')) {
            $recommendations[] = $this->getOverspentMessage();
        }

        // Savings progress
        if ($analyzer->hasPattern('nearGoal')) {
            $recommendations[] = $this->getNearGoalMessage();
        } elseif ($analyzer->hasPattern('lowSavingsProgress')) {
            $recommendations[] = $this->getLowSavingsProgressMessage();
        }

        // If on track and good progress
        if ($analyzer->hasPattern('onTrack')) {
            $recommendations[] = $this->getOnTrackFullMessage();
        }

        return $recommendations;
    }

    /**
     * Store categoryBreakdown for pattern analysis
     */
    private $categoryBreakdown = null;

    /**
     * Pesan jika tidak ada pengeluaran
     */
    protected function getNoExpenseMessage(): string
    {
        $messages = [
            "Belum ada pengeluaran minggu ini. Parah banget atau memang disimpan semua? 😄",
            "Wow, pengeluaran Anda 0? Jadi Anda menabung 100% minggu ini! Keren 💪",
            "Tidak ada transaksi minggu ini. Keep it up atau ada transaksi yang lupa dicatat? 📝",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Opening message berdasarkan total pengeluaran
     */
    protected function getOpeningMessage(float $totalExpense): string
    {
        $formatted = 'Rp ' . number_format($totalExpense, 0, ',', '.');

        $messages = [
            "Hai! 👋 Minggu ini total pengeluaranmu sekitar {$formatted}. Yuk kita lihat breakdown-nya:",
            "Halo! 🌟 Dalam 7 hari terakhir, kamu sudah mengeluarkan {$formatted}. Ini analisisku:",
            "Hey there! ✨ Pengeluaranmu minggu ini mencapai {$formatted}. Ini insight dariku:",
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk frequent online food order (Shopee/GoFood/GrabFood detected)
     */
    protected function getFrequentOnlineFoodMessage(): string
    {
        $messages = [
            "🍜 Kamu sering pesan makan online minggu ini. Ongkirnya lumayan lho. Coba sesekali beli langsung ke warung atau masak sederhana sendiri.",
            "📱 Banyak order GoFood/GrabFood/ShopeeFood ya? Ongkir + biaya layanan itu numpuk lho. Sesekali jalan ke warung bisa lebih hemat.",
            "🛍️ Online food memang praktis, tapi ongkirnya bikin budget makan membengkak. Coba kurangi 2-3 order per minggu?",
            "🍕 Keseringan pesan makanan online nih. Coba hitung: ongkir Rp10rb x 5 = Rp50rb/minggu. Lumayan kan buat ditabung?",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk high food spending tanpa online delivery
     */
    protected function getHighFoodSpendingMessage(): string
    {
        $messages = [
            "🍱 Pengeluaran makan cukup besar minggu ini. Mungkin bisa coba meal prep agar lebih hemat.",
            "🍽️ Budget makan tinggi nih. Coba deh masak sendiri di weekend, simpan untuk makan siang weekday.",
            "🥘 Makan emang kebutuhan pokok, tapi kalau >50% dari total pengeluaran, coba cari alternatif lebih hemat.",
            "🍲 Porsi makan lumayan besar di budget. Tips: bawa bekal dari rumah bisa hemat sampai 50%.",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk balanced food spending
     */
    protected function getBalancedFoodSpendingMessage(): string
    {
        $messages = [
            "✅ Pengeluaran makan kamu seimbang. Terusin aja pola makan ini!",
            "👍 Budget untuk makanan terlihat sehat. Good job mempertahankan balance.",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk high hangout spending (>20%)
     */
    protected function getHighHangoutMessage(): string
    {
        $messages = [
            "☕ Nongkrong itu seru, tapi terlalu sering di cafe bikin budget cepat habis. Sesekali hangout di kos atau taman bisa lebih hemat.",
            "🎉 Seru sih main sama temen, tapi pengeluaran nongkrong udah >20% nih. Coba alternatif gratis kayak piknik atau main di rumah.",
            "🎮 Hangout budget lumayan besar. Tips: gantian tempat nongkrong, ga harus selalu di cafe mahal.",
            "☕ Cafe hopping emang asik, tapi dompet bisa nangis. Sekali-kali ngopi di rumah temen juga seru kok!",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk balanced hangout spending
     */
    protected function getBalancedHangoutMessage(): string
    {
        $messages = [
            "✅ Hangoutan kamu reasonable. Balance antara fun dan saving bagus!",
            "👍 Tidak terlalu banyak keluar, tapi juga jangan terlalu kekang diri. Good balance!",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk heavy transport spending (>20%)
     */
    protected function getHeavyTransportMessage(): string
    {
        $messages = [
            "🚌 Biaya transport cukup tinggi. Kalau jarak dekat, coba jalan kaki atau sepeda. Lebih sehat juga!",
            "🚲 Transport >20% dari pengeluaran nih. Coba kombinasi: ojol untuk jarak jauh, jalan kaki untuk dekat.",
            "🚗 Ongkos perjalanan lumayan besar. Tips: cari promo ojol atau nebeng bareng temen.",
            "🛵 Budget transport tinggi. Kalau memungkinkan, naik angkot/busway bisa hemat sampai 50%.",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk balanced transport spending
     */
    protected function getBalancedTransportMessage(): string
    {
        $messages = [
            "✅ Biaya transport sehat dan efisien.",
            "👍 Transport dalam range ideal.",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk overspending weekly/monthly budget
     */
    protected function getOverspentMessage(): string
    {
        $messages = [
            "⚠️ Pengeluaranmu minggu ini melewati uang jajan mingguan. Hati-hati agar target tabungan tetap aman.",
            "🚨 Budget minggu ini udah jebol nih. Minggu depan coba lebih ketat ya biar tabungan ga terganggu.",
            "💸 Ups, pengeluaran melebihi budget! Evaluasi mana yang bisa dikurangi supaya saving plan tetap jalan.",
            "⚠️ Over budget minggu ini. Ga apa-apa, yang penting evaluasi dan lebih disiplin minggu depan!",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk near goal (balance >= 80% of goal)
     */
    protected function getNearGoalMessage(): string
    {
        $messages = [
            "🎯 Tabunganmu sudah hampir mencapai target! Tinggal sedikit lagi, pertahankan konsistensi.",
            "🚀 Wow, tabungan udah 80%+ dari target! Sedikit lagi sampai, jangan kendor sekarang!",
            "⭐ Almost there! Tinggal sprint akhir menuju goal. Semangat, kamu pasti bisa! 💪",
            "🌟 Target tabungan di depan mata! Konsisten aja, dalam waktu dekat pasti tercapai.",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk low savings progress (<20% of goal)
     */
    protected function getLowSavingsProgressMessage(): string
    {
        $messages = [
            "🌱 Tabungan masih kecil dibanding target. Coba sisihkan sedikit setiap kali ada pemasukan.",
            "💰 Progress tabungan baru di awal. Tips: sisihkan 10-20% dari uang jajan langsung pas terima.",
            "🌱 Masih jauh dari target, tapi santai aja. Yang penting konsisten nabung tiap minggu.",
            "📈 Tabungan masih <20% dari goal. Yuk, challenge diri sendiri untuk lebih disiplin!",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Message untuk on track (semuanya bagus)
     */
    protected function getOnTrackFullMessage(): string
    {
        $messages = [
            "✨ Kamu on track! Pengeluaran terkontrol dan tabungan berjalan lancar. Maintain aja! 🎉",
            "🎊 Sip! Finansialmu sehat dan sesuai budget. Terusin pola ini ya! 👏",
        ];
        return $messages[array_rand($messages)];
    }

    /**
     * Pesan terkait savings goal
     */
    protected function getSavingsGoalMessage(float $goalAmount, float $currentBalance): string
    {
        $formattedGoal = 'Rp ' . number_format($goalAmount, 0, ',', '.');
        $formattedBalance = 'Rp ' . number_format($currentBalance, 0, ',', '.');
        $percentage = $goalAmount > 0 ? round(($currentBalance / $goalAmount) * 100, 1) : 0;

        if ($percentage >= 100) {
            return "🎉 WOW! Kamu sudah mencapai target tabungan {$formattedGoal}! Saatnya set goal baru yang lebih tinggi!";
        } elseif ($percentage >= 75) {
            return "🔥 Amazing! Kamu sudah di {$percentage}% dari target {$formattedGoal}. Tinggal sedikit lagi!";
        } elseif ($percentage >= 50) {
            return "💪 Halfway there! Tabunganmu sudah {$formattedBalance} dari target {$formattedGoal}. Terus semangat!";
        } elseif ($percentage >= 25) {
            return "🌱 Progress bagus! Sudah {$percentage}% menuju {$formattedGoal}. Konsistensi adalah kunci!";
        } else {
            return "🎯 Target tabunganmu {$formattedGoal}. Yuk mulai sisihkan sedikit demi sedikit, pasti bisa!";
        }
    }

    /**
     * Closing message yang motivating
     */
    protected function getClosingMessage(): string
    {
        $messages = [
            "💡 Ingat, financial wellness itu journey, bukan destination. Small steps matter!",
            "🌈 Yang penting progress, bukan perfection. Kamu sudah di jalur yang benar!",
            "💚 Setiap keputusan kecil hari ini membentuk masa depan finansialmu. You got this!",
            "✨ Tracking keuangan secara rutin itu langkah pertama yang luar biasa. Proud of you!",
            "🚀 Keep going! Awareness adalah step pertama menuju financial freedom.",
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Generate recommendation menggunakan OpenAI (jika API key tersedia)
     * Untuk implementasi masa depan
     */
    public function generateWithOpenAI(array $data): ?string
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            return null; // Fallback ke rule-based
        }

        // TODO: Implementasi OpenAI API call
        // Prompt template untuk OpenAI:
        $prompt = $this->buildOpenAIPrompt($data);

        // Return null untuk saat ini, gunakan rule-based
        return null;
    }

    /**
     * Build prompt untuk OpenAI
     */
    protected function buildOpenAIPrompt(array $data): string
    {
        $breakdown = '';
        foreach ($data['category_percentages'] as $category => $info) {
            $breakdown .= "- {$category}: {$info['percentage']}% (Rp " . number_format($info['total'], 0, ',', '.') . ")\n";
        }

        return <<<PROMPT
Kamu adalah financial advisor yang friendly untuk mahasiswa/anak muda Indonesia.
Berikan rekomendasi keuangan yang santai dan supportive (tidak menggurui) berdasarkan data berikut:

Total Pengeluaran 7 Hari: Rp {$data['total_expense_7_days']}

Breakdown per Kategori:
{$breakdown}

Standar ideal:
- Makan: 40-50%
- Transport: 10-20%
- Nongkrong/Hiburan: 10-20%
- Akademik: 5-10%
- Sisanya untuk tabungan

Target tabungan user: Rp {$data['wallet_setting']->financial_goal}
Balance saat ini: Rp {$data['wallet_setting']->balance}

Berikan rekomendasi dalam bahasa Indonesia yang casual, maksimal 3-4 paragraf pendek.
Tone: supportive, tidak judgmental, actionable.
PROMPT;
    }

    /**
     * Get financial context for AI chat
     * Returns a summary of user's financial situation
     * 
     * NOW USES FinancialSummaryService for single source of truth
     */
    public function getFinancialContext(int $userId): array
    {
        // Use the centralized financial service
        $financialService = app(FinancialSummaryService::class);
        return $financialService->getAIContext($userId);
    }

    /**
     * Build context string for AI chat
     */
    public function buildContextString(array $context): string
    {
        $balance = 'Rp ' . number_format($context['balance'], 0, ',', '.');
        $monthlyExpense = 'Rp ' . number_format($context['monthly_expense'], 0, ',', '.');
        $weeklyExpense = 'Rp ' . number_format($context['weekly_expense'], 0, ',', '.');
        $monthlyIncome = 'Rp ' . number_format($context['monthly_income'], 0, ',', '.');
        
        $contextStr = "DATA KEUANGAN USER:\n";
        $contextStr .= "- Saldo saat ini: {$balance}\n";
        $contextStr .= "- Pemasukan bulan ini: {$monthlyIncome}\n";
        $contextStr .= "- Pengeluaran bulan ini: {$monthlyExpense}\n";
        $contextStr .= "- Pengeluaran 7 hari terakhir: {$weeklyExpense}\n";
        
        if ($context['monthly_allowance']) {
            $contextStr .= "- Uang jajan bulanan: Rp " . number_format($context['monthly_allowance'], 0, ',', '.') . "\n";
        }
        if ($context['weekly_allowance']) {
            $contextStr .= "- Uang jajan mingguan: Rp " . number_format($context['weekly_allowance'], 0, ',', '.') . "\n";
        }
        if ($context['financial_goal']) {
            $contextStr .= "- Target tabungan: Rp " . number_format($context['financial_goal'], 0, ',', '.') . "\n";
        }

        // Category breakdown
        if (!empty($context['category_breakdown'])) {
            $contextStr .= "\nPENGELUARAN PER KATEGORI BULAN INI:\n";
            foreach ($context['category_breakdown'] as $cat) {
                $amount = 'Rp ' . number_format($cat['total'], 0, ',', '.');
                $contextStr .= "- {$cat['name']}: {$amount} ({$cat['count']} transaksi)\n";
            }
        }

        // Saving goals
        if (!empty($context['saving_goals'])) {
            $contextStr .= "\nTARGET TABUNGAN AKTIF:\n";
            foreach ($context['saving_goals'] as $goal) {
                $target = 'Rp ' . number_format($goal['target'], 0, ',', '.');
                $current = 'Rp ' . number_format($goal['current'], 0, ',', '.');
                $deadline = $goal['deadline'] ? " (deadline: {$goal['deadline']})" : '';
                $contextStr .= "- {$goal['name']}: {$current} / {$target} ({$goal['progress']}%){$deadline}\n";
            }
        }

        return $contextStr;
    }

    /**
     * Chat with AI - process user message and return AI response
     * This uses rule-based responses with context awareness
     */
    public function chatWithAI(string $message, array $context, array $chatHistory = []): string
    {
        $message = strtolower(trim($message));
        
        // Build context string for reference
        $contextString = $this->buildContextString($context);
        
        // Detect intent from message
        $intent = $this->detectIntent($message);
        
        // Generate response based on intent
        return $this->generateChatResponse($intent, $message, $context, $chatHistory);
    }

    /**
     * Detect user intent from message
     */
    protected function detectIntent(string $message): string
    {
        // Balance/Saldo related
        if (preg_match('/saldo|balance|uang\s*(saya|ku|gue)?|duit/i', $message)) {
            return 'balance';
        }
        
        // Spending/Pengeluaran related
        if (preg_match('/pengeluaran|spending|habis|boros|keluar/i', $message)) {
            return 'spending';
        }
        
        // Income/Pemasukan related
        if (preg_match('/pemasukan|income|pendapatan|masuk|terima/i', $message)) {
            return 'income';
        }
        
        // Saving/Tabungan related
        if (preg_match('/tabung|saving|target|goal|nabung|simpan/i', $message)) {
            return 'savings';
        }
        
        // Tips/Advice related
        if (preg_match('/tips|saran|rekomendasi|advice|gimana|bagaimana|cara|hemat/i', $message)) {
            return 'tips';
        }
        
        // Category breakdown
        if (preg_match('/kategori|breakdown|detail|rinci/i', $message)) {
            return 'category';
        }
        
        // Budget related
        if (preg_match('/budget|anggaran|jajan|allowance/i', $message)) {
            return 'budget';
        }
        
        // Greeting
        if (preg_match('/^(hai|halo|hi|hello|hey|pagi|siang|sore|malam)/i', $message)) {
            return 'greeting';
        }
        
        // Thanks
        if (preg_match('/terima\s*kasih|thanks|makasih|thx/i', $message)) {
            return 'thanks';
        }
        
        // Help
        if (preg_match('/help|bantu|bisa\s*apa|fitur/i', $message)) {
            return 'help';
        }
        
        return 'general';
    }

    /**
     * Generate chat response based on intent
     */
    protected function generateChatResponse(string $intent, string $message, array $context, array $chatHistory): string
    {
        switch ($intent) {
            case 'greeting':
                return $this->getGreetingResponse();
                
            case 'thanks':
                return $this->getThanksResponse();
                
            case 'help':
                return $this->getHelpResponse();
                
            case 'balance':
                return $this->getBalanceResponse($context);
                
            case 'spending':
                return $this->getSpendingResponse($context);
                
            case 'income':
                return $this->getIncomeResponse($context);
                
            case 'savings':
                return $this->getSavingsResponse($context);
                
            case 'tips':
                return $this->getTipsResponse($context);
                
            case 'category':
                return $this->getCategoryResponse($context);
                
            case 'budget':
                return $this->getBudgetResponse($context);
                
            default:
                return $this->getGeneralResponse($context);
        }
    }

    protected function getGreetingResponse(): string
    {
        $greetings = [
            "Hai! 👋 Aku YourMoment AI Assistant. Ada yang bisa kubantu soal keuanganmu hari ini?",
            "Halo! 🌟 Senang bisa ngobrol denganmu. Mau tanya apa tentang finansialmu?",
            "Hey there! ✨ Aku siap bantu kamu manage keuangan. Ada pertanyaan?",
        ];
        return $greetings[array_rand($greetings)];
    }

    protected function getThanksResponse(): string
    {
        $responses = [
            "Sama-sama! 😊 Kalau ada pertanyaan lagi, jangan sungkan ya!",
            "My pleasure! 🌟 Semoga tips-nya membantu. Chat lagi kalau butuh bantuan!",
            "You're welcome! 💚 Keep tracking dan semangat nabungnya!",
        ];
        return $responses[array_rand($responses)];
    }

    protected function getHelpResponse(): string
    {
        return "Aku bisa bantu kamu dengan:\n\n" .
            "💰 **Cek saldo** - Tanya saldo atau uang kamu\n" .
            "📊 **Analisis pengeluaran** - Lihat pola spending\n" .
            "💵 **Info pemasukan** - Rangkuman income\n" .
            "🎯 **Target tabungan** - Progress saving goals\n" .
            "💡 **Tips hemat** - Rekomendasi keuangan\n" .
            "📈 **Breakdown kategori** - Detail per kategori\n\n" .
            "Coba tanya: \"Berapa saldo saya?\" atau \"Tips hemat dong!\"";
    }

    protected function getBalanceResponse(array $context): string
    {
        $balance = 'Rp ' . number_format($context['balance'], 0, ',', '.');
        
        $responses = [
            "💰 Saldo kamu saat ini: **{$balance}**\n\nMau tau breakdown pengeluarannya?",
            "Saldo di dompet: **{$balance}** 💵\n\nPerlu tips untuk mengaturnya?",
        ];
        
        $response = $responses[array_rand($responses)];
        
        // Add context if balance is low
        if ($context['balance'] < 100000) {
            $response .= "\n\n⚠️ Saldo agak tipis nih. Mungkin waktunya top up atau kurangi pengeluaran.";
        }
        
        return $response;
    }

    protected function getSpendingResponse(array $context): string
    {
        $monthly = 'Rp ' . number_format($context['monthly_expense'], 0, ',', '.');
        $weekly = 'Rp ' . number_format($context['weekly_expense'], 0, ',', '.');
        
        $response = "📊 **Analisis Pengeluaran:**\n\n";
        $response .= "• Bulan ini: {$monthly}\n";
        $response .= "• 7 hari terakhir: {$weekly}\n\n";
        
        // Add top categories
        if (!empty($context['category_breakdown'])) {
            $response .= "**Top Kategori:**\n";
            $topCats = array_slice($context['category_breakdown'], 0, 3);
            foreach ($topCats as $cat) {
                $amount = 'Rp ' . number_format($cat['total'], 0, ',', '.');
                $response .= "• {$cat['name']}: {$amount}\n";
            }
        }
        
        // Add insight
        if ($context['weekly_allowance'] && $context['weekly_expense'] > $context['weekly_allowance']) {
            $response .= "\n⚠️ Pengeluaran mingguan sudah melebihi budget!";
        } else {
            $response .= "\n✅ Keep tracking ya!";
        }
        
        return $response;
    }

    protected function getIncomeResponse(array $context): string
    {
        $monthly = 'Rp ' . number_format($context['monthly_income'], 0, ',', '.');
        $expense = 'Rp ' . number_format($context['monthly_expense'], 0, ',', '.');
        $net = $context['monthly_income'] - $context['monthly_expense'];
        $netFormatted = 'Rp ' . number_format(abs($net), 0, ',', '.');
        
        $response = "💵 **Pemasukan Bulan Ini:** {$monthly}\n\n";
        $response .= "Pengeluaran: {$expense}\n";
        
        if ($net >= 0) {
            $response .= "Sisa: +{$netFormatted} ✅\n\n";
            $response .= "Bagus! Kamu masih surplus bulan ini. 💪";
        } else {
            $response .= "Defisit: -{$netFormatted} ⚠️\n\n";
            $response .= "Hmm, pengeluaran lebih besar dari pemasukan. Coba evaluasi spending-nya ya!";
        }
        
        return $response;
    }

    protected function getSavingsResponse(array $context): string
    {
        if (empty($context['saving_goals'])) {
            return "🎯 Kamu belum punya target tabungan aktif.\n\n" .
                "Yuk bikin target! Misalnya:\n" .
                "• Beli gadget baru\n" .
                "• Dana darurat\n" .
                "• Liburan\n\n" .
                "Pergi ke menu **Target** untuk mulai! 💪";
        }
        
        $response = "🎯 **Target Tabungan Aktif:**\n\n";
        
        foreach ($context['saving_goals'] as $goal) {
            $target = 'Rp ' . number_format($goal['target'], 0, ',', '.');
            $current = 'Rp ' . number_format($goal['current'], 0, ',', '.');
            $progressBar = $this->makeProgressBar($goal['progress']);
            
            $response .= "**{$goal['name']}**\n";
            $response .= "{$progressBar} {$goal['progress']}%\n";
            $response .= "{$current} / {$target}\n\n";
        }
        
        $response .= "Terus semangat nabungnya! 💪";
        
        return $response;
    }

    protected function getTipsResponse(array $context): string
    {
        $tips = [];
        
        // Analyze and give contextual tips
        if (!empty($context['category_breakdown'])) {
            $totalExpense = array_sum(array_column($context['category_breakdown'], 'total'));
            
            foreach ($context['category_breakdown'] as $cat) {
                $percentage = $totalExpense > 0 ? ($cat['total'] / $totalExpense) * 100 : 0;
                
                if ($cat['name'] === 'Makan' && $percentage > 50) {
                    $tips[] = "🍱 Pengeluaran makan >50%. Coba meal prep di weekend untuk hemat!";
                }
                if ($cat['name'] === 'Nongkrong' && $percentage > 20) {
                    $tips[] = "☕ Budget nongkrong cukup besar. Sesekali hangout di tempat gratis!";
                }
                if ($cat['name'] === 'Transport' && $percentage > 20) {
                    $tips[] = "🚌 Transport lumayan tinggi. Coba kombinasi jalan kaki untuk jarak dekat.";
                }
            }
        }
        
        // General tips if no specific issues
        if (empty($tips)) {
            $generalTips = [
                "💡 Sisihkan 20% dari pemasukan untuk tabungan sebelum dipakai.",
                "📝 Catat setiap pengeluaran, sekecil apapun. Awareness is key!",
                "🎯 Set target tabungan yang spesifik, lebih mudah tercapai.",
                "⏰ Tunggu 24 jam sebelum beli barang non-esensial.",
                "🍱 Bawa bekal bisa hemat sampai 50% budget makan!",
            ];
            $tips[] = $generalTips[array_rand($generalTips)];
            $tips[] = $generalTips[array_rand($generalTips)];
        }
        
        return "💡 **Tips Keuangan:**\n\n" . implode("\n\n", array_unique($tips));
    }

    protected function getCategoryResponse(array $context): string
    {
        if (empty($context['category_breakdown'])) {
            return "📊 Belum ada data pengeluaran bulan ini.\n\nMulai catat transaksimu di menu Transaksi!";
        }
        
        $response = "📊 **Breakdown Pengeluaran Bulan Ini:**\n\n";
        
        $totalExpense = array_sum(array_column($context['category_breakdown'], 'total'));
        
        foreach ($context['category_breakdown'] as $cat) {
            $amount = 'Rp ' . number_format($cat['total'], 0, ',', '.');
            $percentage = $totalExpense > 0 ? round(($cat['total'] / $totalExpense) * 100, 1) : 0;
            $response .= "• **{$cat['name']}**: {$amount} ({$percentage}%)\n";
        }
        
        $total = 'Rp ' . number_format($totalExpense, 0, ',', '.');
        $response .= "\n**Total:** {$total}";
        
        return $response;
    }

    protected function getBudgetResponse(array $context): string
    {
        $response = "💵 **Info Budget:**\n\n";
        
        if ($context['monthly_allowance']) {
            $monthly = 'Rp ' . number_format($context['monthly_allowance'], 0, ',', '.');
            $spent = 'Rp ' . number_format($context['monthly_expense'], 0, ',', '.');
            $remaining = $context['monthly_allowance'] - $context['monthly_expense'];
            $remainingFormatted = 'Rp ' . number_format(abs($remaining), 0, ',', '.');
            
            $response .= "Uang jajan bulanan: {$monthly}\n";
            $response .= "Sudah dipakai: {$spent}\n";
            $response .= "Sisa: " . ($remaining >= 0 ? "+{$remainingFormatted} ✅" : "-{$remainingFormatted} ⚠️") . "\n\n";
        }
        
        if ($context['weekly_allowance']) {
            $weekly = 'Rp ' . number_format($context['weekly_allowance'], 0, ',', '.');
            $spent = 'Rp ' . number_format($context['weekly_expense'], 0, ',', '.');
            $remaining = $context['weekly_allowance'] - $context['weekly_expense'];
            $remainingFormatted = 'Rp ' . number_format(abs($remaining), 0, ',', '.');
            
            $response .= "Uang jajan mingguan: {$weekly}\n";
            $response .= "Sudah dipakai (7 hari): {$spent}\n";
            $response .= "Status: " . ($remaining >= 0 ? "On budget ✅" : "Over budget ⚠️");
        }
        
        if (!$context['monthly_allowance'] && !$context['weekly_allowance']) {
            $response .= "Kamu belum set budget. Pergi ke Profil > Pengaturan Wallet untuk set uang jajan bulanan/mingguan!";
        }
        
        return $response;
    }

    protected function getGeneralResponse(array $context): string
    {
        $responses = [
            "Hmm, aku kurang paham pertanyaannya. 🤔\n\nCoba tanya tentang:\n• Saldo kamu\n• Pengeluaran minggu/bulan ini\n• Target tabungan\n• Tips hemat",
            "Maaf, aku belum bisa jawab itu. 😅\n\nAku bisa bantu:\n• Cek saldo\n• Analisis spending\n• Info saving goals\n• Tips keuangan",
            "Pertanyaan menarik! Tapi aku fokus di keuangan aja ya. 💰\n\nMau tau saldo, pengeluaran, atau tips hemat?",
        ];
        
        return $responses[array_rand($responses)];
    }

    /**
     * Make a simple text progress bar
     */
    protected function makeProgressBar(float $percentage): string
    {
        $filled = (int) ($percentage / 10);
        $empty = 10 - $filled;
        return str_repeat('█', $filled) . str_repeat('░', $empty);
    }
}
