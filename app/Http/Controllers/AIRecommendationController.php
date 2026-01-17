<?php

namespace App\Http\Controllers;

use App\Services\AIRecommendationService;
use App\Services\ChatIntentDetector;
use App\Services\ChatSessionMemoryService;
use App\Services\FutureBudgetPlanningService;
use App\Services\GoalSimulationService;
use App\Services\FinancialAnalysisService;
use Illuminate\Http\Request;

class AIRecommendationController extends Controller
{
    protected AIRecommendationService $aiService;
    protected ChatIntentDetector $intentDetector;
    protected GoalSimulationService $goalSimulationService;
    protected ChatSessionMemoryService $sessionMemory;
    protected FutureBudgetPlanningService $futureBudgetService;
    protected FinancialAnalysisService $analysisService;

    public function __construct(
        AIRecommendationService $aiService,
        ChatIntentDetector $intentDetector,
        GoalSimulationService $goalSimulationService,
        ChatSessionMemoryService $sessionMemory,
        FutureBudgetPlanningService $futureBudgetService,
        FinancialAnalysisService $analysisService
    ) {
        $this->aiService = $aiService;
        $this->intentDetector = $intentDetector;
        $this->goalSimulationService = $goalSimulationService;
        $this->sessionMemory = $sessionMemory;
        $this->futureBudgetService = $futureBudgetService;
        $this->analysisService = $analysisService;
    }

    /**
     * Display Financial Analysis page
     * 
     * Supports 3 modes:
     * - 'monthly' (default): Analisis bulanan dari database
     * - 'weekly': Analisis mingguan dari database
     * - 'simulation': Analisis dari input manual (tidak menyentuh database)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get period mode from query (default: monthly)
        $periodMode = $request->query('period', 'monthly');
        if (!in_array($periodMode, ['weekly', 'monthly', 'simulation'])) {
            $periodMode = 'monthly';
        }

        try {
            // Mode SIMULASI: tampilkan form, tidak ada analisis awal
            if ($periodMode === 'simulation') {
                return view('ai-recommendation', $this->getSimulationViewData());
            }

            // Mode WEEKLY / MONTHLY: ambil data dari database
            $analysis = $this->analysisService->generateAnalysis($user->id, $periodMode);

            return view('ai-recommendation', [
                // Period info
                'periodMode' => $periodMode,
                'period' => $analysis['period'],
                'comparisonPeriod' => $analysis['comparison_period'],
                'isSimulation' => false,

                // Financial data (BERBASIS PERIODE)
                'current' => $analysis['current'],
                'previous' => $analysis['previous'],
                'expenseRatio' => $analysis['expense_ratio'],
                'timeRatio' => $analysis['time_ratio'],

                // Saldo total (VISUAL ONLY)
                'totalBalance' => $analysis['total_balance'],
                'totalBalanceNote' => $analysis['total_balance_note'] ?? '',

                // Legacy vars
                'totalExpense' => $analysis['current']['total_expense'],
                'totalIncome' => $analysis['current']['total_income'],

                // Daily patterns & recommendations
                'dailyPatterns' => $analysis['daily_patterns'],
                'dailyRecommendation' => $analysis['daily_recommendation'],

                // Analysis results
                'trend' => $analysis['trend'],
                'composition' => $analysis['composition'],
                'patterns' => $analysis['patterns'],
                'insights' => $analysis['insights'],
                'warnings' => $analysis['warnings'],

                // Health score
                'healthScore' => $analysis['health_score'],

                // Categories
                'topCategories' => $analysis['top_categories'],

                // Full analysis
                'walletSetting' => $user->walletSetting,
                'analysis' => $analysis,

                // Simulation input (kosong untuk mode non-simulasi)
                'simulationInput' => null,
            ]);
        } catch (\Exception $e) {
            return view('ai-recommendation', $this->getErrorViewData($periodMode, $e->getMessage()));
        }
    }

    /**
     * Process simulation form submission
     * Data di-input manual, TIDAK menyentuh database
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'income' => ['required', 'numeric', 'min:0'],
            'expense' => ['required', 'numeric', 'min:0'],
            'days_remaining' => ['required', 'integer', 'min:1', 'max:365'],
            'total_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        try {
            $input = [
                'income' => $request->input('income'),
                'expense' => $request->input('expense'),
                'days_remaining' => $request->input('days_remaining'),
                'total_days' => $request->input('total_days', 30),
            ];

            // Generate analisis simulasi (TIDAK menyentuh database)
            $analysis = $this->analysisService->generateSimulationAnalysis($input);

            return view('ai-recommendation', [
                // Period info
                'periodMode' => 'simulation',
                'period' => $analysis['period'],
                'comparisonPeriod' => null,
                'isSimulation' => true,

                // Financial data
                'current' => $analysis['current'],
                'previous' => null,
                'expenseRatio' => $analysis['expense_ratio'],
                'timeRatio' => $analysis['time_ratio'],

                // Saldo total tidak ada di simulasi
                'totalBalance' => null,
                'totalBalanceNote' => 'Mode simulasi tidak menampilkan saldo total.',

                // Legacy vars
                'totalExpense' => $analysis['current']['total_expense'],
                'totalIncome' => $analysis['current']['total_income'],

                // Daily patterns & recommendations
                'dailyPatterns' => $analysis['daily_patterns'],
                'dailyRecommendation' => $analysis['daily_recommendation'],

                // Analysis results
                'trend' => $analysis['trend'],
                'composition' => $analysis['composition'],
                'patterns' => $analysis['patterns'],
                'insights' => $analysis['insights'],
                'warnings' => $analysis['warnings'],

                // Health score
                'healthScore' => $analysis['health_score'],

                // Categories (kosong di simulasi)
                'topCategories' => [],

                // Full analysis
                'walletSetting' => null,
                'analysis' => $analysis,

                // Simulation input (untuk re-populate form)
                'simulationInput' => $input,
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses simulasi: ' . $e->getMessage());
        }
    }

    /**
     * Get view data untuk mode simulasi (tanpa hasil)
     */
    private function getSimulationViewData(): array
    {
        return [
            'periodMode' => 'simulation',
            'period' => ['mode' => 'simulation', 'label' => 'Simulasi', 'detail' => 'Masukkan data untuk simulasi', 'days_remaining' => 0, 'days_elapsed' => 0, 'total_days' => 30],
            'comparisonPeriod' => null,
            'isSimulation' => true,
            'current' => null,
            'previous' => null,
            'expenseRatio' => 0,
            'timeRatio' => 0,
            'totalBalance' => null,
            'totalBalanceNote' => 'Mode simulasi tidak menampilkan saldo total.',
            'totalExpense' => 0,
            'totalIncome' => 0,
            'dailyPatterns' => null,
            'dailyRecommendation' => null,
            'trend' => null,
            'composition' => null,
            'patterns' => null,
            'insights' => null,
            'warnings' => null,
            'healthScore' => null,
            'topCategories' => [],
            'walletSetting' => null,
            'analysis' => null,
            'simulationInput' => null,
        ];
    }

    /**
     * Get view data untuk error state
     */
    private function getErrorViewData(string $periodMode, string $errorMessage = ''): array
    {
        return [
            'periodMode' => $periodMode,
            'period' => ['mode' => $periodMode, 'label' => 'Error', 'detail' => 'Terjadi kesalahan', 'days_remaining' => 0, 'days_elapsed' => 0, 'total_days' => 0],
            'comparisonPeriod' => null,
            'isSimulation' => false,
            'current' => ['total_income' => 0, 'total_expense' => 0, 'balance' => 0, 'remaining_budget' => 0, 'transaction_count' => 0],
            'previous' => null,
            'expenseRatio' => 0,
            'timeRatio' => 0,
            'totalBalance' => 0,
            'totalBalanceNote' => '',
            'totalExpense' => 0,
            'totalIncome' => 0,
            'dailyPatterns' => ['average_daily' => 0, 'spending_consistency' => 'no_data'],
            'dailyRecommendation' => ['recommended_daily' => 0, 'current_daily_avg' => 0, 'remaining_budget' => 0, 'days_remaining' => 0, 'status' => 'no_data', 'status_label' => '-', 'category_recommendations' => []],
            'trend' => ['has_previous_data' => false],
            'composition' => [],
            'patterns' => [],
            'insights' => [[
                'type' => 'danger',
                'icon' => 'x-circle',
                'title' => 'Terjadi Kesalahan',
                'text' => 'Maaf, terjadi kendala saat memproses data keuanganmu. Silakan coba lagi nanti.',
            ]],
            'warnings' => [[
                'level' => 'info',
                'title' => 'Kesalahan Sistem',
                'message' => 'Tidak dapat memuat data analisis.',
                'action' => 'Coba refresh halaman ini.',
            ]],
            'healthScore' => ['score' => 0, 'label' => 'Error', 'color' => 'gray', 'breakdown' => []],
            'topCategories' => [],
            'walletSetting' => null,
            'analysis' => [],
            'simulationInput' => null,
        ];
    }

    /**
     * Display AI Chat interface
     */
    public function chat()
    {
        $user = auth()->user();

        // Get financial context for AI
        $financialContext = $this->aiService->getFinancialContext($user->id);

        // Get chat history from session
        $chatHistory = session('ai_chat_history', []);

        return view('ai.chat', [
            'financialContext' => $financialContext,
            'chatHistory' => $chatHistory,
        ]);
    }

    /**
     * Send message to AI and get response
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user = auth()->user();
        $userMessage = $request->input('message');

        // Handle clear history command
        if ($userMessage === '__clear_history__') {
            session()->forget('ai_chat_history');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'response' => 'Chat history cleared.',
                ]);
            }
            // Also clear session memory
            $this->sessionMemory->clearPendingState();
            return back();
        }

        try {
            // Get financial context
            $context = $this->aiService->getFinancialContext($user->id);

            // Get chat history for context
            $chatHistory = session('ai_chat_history', []);

            // =====================================================
            // CHECK CONVERSATION STATE FIRST
            // =====================================================

            // Cek apakah ada pending goal simulation yang menunggu income input
            if ($this->sessionMemory->isAwaitingIncomeInput()) {
                $aiResponse = $this->handlePendingIncomeInput($userMessage, $context);
            } else {
                // Normal flow: Detect MULTIPLE intents
                $intents = $this->intentDetector->detectMultiple($userMessage);

                // Generate AI response untuk setiap intent dan gabungkan
                $aiResponse = $this->processMultipleIntents($intents, $userMessage, $context, $chatHistory);
            }

            // Store in session
            $chatHistory[] = [
                'role' => 'user',
                'content' => $userMessage,
                'timestamp' => now()->toISOString(),
            ];
            $chatHistory[] = [
                'role' => 'assistant',
                'content' => $aiResponse,
                'timestamp' => now()->toISOString(),
            ];

            // Keep only last 20 messages
            if (count($chatHistory) > 20) {
                $chatHistory = array_slice($chatHistory, -20);
            }

            session(['ai_chat_history' => $chatHistory]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'response' => $aiResponse,
                ]);
            }

            return back();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'response' => 'Maaf, ada kendala saat memproses pesanmu. Silakan coba lagi.',
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim pesan ke AI');
        }
    }

    /**
     * Handle pending income input untuk melanjutkan goal simulation
     * 
     * @param string $message User's message (should contain income amount)
     * @param array $context Financial context
     * @return string AI response
     */
    protected function handlePendingIncomeInput(string $message, array $context): string
    {
        // Coba extract nominal dari message
        $amounts = GoalSimulationService::extractAmounts($message);
        $monthlyIncome = $amounts['monthly'] ?? $amounts['target'] ?? null;

        // Jika tidak ada nominal, coba parse langsung
        if ($monthlyIncome === null) {
            $monthlyIncome = GoalSimulationService::parseNominal($message);
        }

        // Jika masih tidak ada nominal yang valid
        if ($monthlyIncome === null || $monthlyIncome <= 0) {
            // Cek apakah user ingin cancel
            if (preg_match('/batal|cancel|ga jadi|gak jadi|skip/i', $message)) {
                $this->sessionMemory->clearPendingState();
                return "Oke, simulasi dibatalkan! 👍\n\nAda yang lain yang bisa kubantu?";
            }

            $targetsString = $this->sessionMemory->formatPendingTargetsString();
            return "🤔 Hmm, aku belum bisa menangkap nominal penghasilanmu.\n\n" .
                "Kamu ingin membeli: {$targetsString}\n\n" .
                "Coba ketik angka penghasilanmu, contoh:\n" .
                "• \"2jt\" atau \"2 juta\"\n" .
                "• \"1.5jt sebulan\"\n" .
                "• \"500rb per minggu\"\n\n" .
                "Atau ketik \"batal\" untuk membatalkan.";
        }

        // Get pending targets
        $pendingTargets = $this->sessionMemory->getPendingTargets();
        $categoryBreakdown = $context['category_breakdown'] ?? null;

        // Clear state before running simulation
        $this->sessionMemory->clearPendingState();

        // Run simulation dengan pending targets
        if (count($pendingTargets) > 1) {
            return $this->goalSimulationService->simulateMultipleTargets($pendingTargets, $monthlyIncome, $categoryBreakdown);
        } else {
            $totalTarget = GoalSimulationService::calculateTotalTarget($pendingTargets);
            return $this->goalSimulationService->simulate($totalTarget, $monthlyIncome, $categoryBreakdown);
        }
    }

    /**
     * Process multiple intents and combine responses
     * 
     * @param array $intents Array of detected intents (can be string or array with metadata)
     * @param string $message Original user message
     * @param array $context Financial context
     * @param array $chatHistory Chat history
     * @return string Combined response
     */
    protected function processMultipleIntents(array $intents, string $message, array $context, array $chatHistory): string
    {
        $responses = [];

        foreach ($intents as $intent) {
            // Intent bisa string atau array dengan metadata
            $intentType = is_array($intent) ? ($intent['type'] ?? 'recommendation') : $intent;
            $intentMeta = is_array($intent) ? $intent : [];

            $response = $this->processIntent($intentType, $message, $context, $chatHistory, $intentMeta);
            if (!empty($response)) {
                $responses[] = $response;
            }
        }

        // Jika hanya 1 intent, return langsung
        if (count($responses) === 1) {
            return $responses[0];
        }

        // Gabungkan multiple responses dengan separator
        if (count($responses) > 1) {
            return implode("\n\n---\n\n", $responses);
        }

        // Fallback
        return $this->aiService->generateRecommendationResponse($message, $context, $chatHistory);
    }

    /**
     * Process single intent and return response
     * 
     * @param string $intent Intent type
     * @param string $message Original message
     * @param array $context Financial context
     * @param array $chatHistory Chat history
     * @param array $meta Additional metadata (e.g., category filter)
     */
    protected function processIntent(string $intent, string $message, array $context, array $chatHistory, array $meta = []): string
    {
        return match ($intent) {
            'goal_simulation' => $this->handleGoalSimulation($message, $context),
            'future_budget_planning' => $this->handleFutureBudgetPlanning($message, $meta),
            'recommendation' => $this->aiService->generateRecommendationResponse($message, $context, $chatHistory),
            'report_saldo' => $this->handleReportSaldo($context),
            'report_pengeluaran' => $this->handleReportPengeluaran($context, $meta['category'] ?? null, $meta['searchKeyword'] ?? null),
            'report_pemasukan' => $this->handleReportPemasukan($context),
            'report_kategori' => $this->handleReportKategori($context),
            'greeting' => $this->handleGreeting(),
            'help' => $this->handleHelp(),
            default => $this->aiService->chatWithAI($message, $context, $chatHistory),
        };
    }

    /**
     * Handle report saldo intent
     */
    protected function handleReportSaldo(array $context): string
    {
        $balance = 'Rp ' . number_format($context['balance'] ?? 0, 0, ',', '.');

        $response = "💰 **Saldo Kamu Saat Ini:**\n\n";
        $response .= "**{$balance}**\n\n";

        // Add context based on balance
        $balanceAmount = $context['balance'] ?? 0;
        if ($balanceAmount < 50000) {
            $response .= "⚠️ Saldo tipis nih! Mungkin saatnya top up atau kurangi pengeluaran.";
        } elseif ($balanceAmount < 100000) {
            $response .= "📊 Saldo mulai menipis. Keep tracking ya!";
        } else {
            $response .= "✅ Saldo masih aman! Tetap jaga keuanganmu ya.";
        }

        return $response;
    }

    /**
     * Handle report pengeluaran intent
     * 
     * @param array $context Financial context
     * @param string|null $category Category filter (null = semua kategori)
     * @param string|null $searchKeyword Search keyword untuk filter by description
     */
    protected function handleReportPengeluaran(array $context, ?string $category = null, ?string $searchKeyword = null): string
    {
        $user = auth()->user();

        // PRIORITY 1: Jika ada category filter, query berdasarkan kategori
        if ($category !== null) {
            return $this->handleReportPengeluaranByCategory($user->id, $category);
        }

        // PRIORITY 2: Jika ada searchKeyword, query berdasarkan description
        if ($searchKeyword !== null) {
            return $this->handleReportPengeluaranByDescription($user->id, $searchKeyword);
        }

        // Default: tampilkan semua kategori
        $monthly = 'Rp ' . number_format($context['monthly_expense'] ?? 0, 0, ',', '.');
        $weekly = 'Rp ' . number_format($context['weekly_expense'] ?? 0, 0, ',', '.');

        $response = "📊 **Pengeluaran Kamu:**\n\n";
        $response .= "• Bulan ini: **{$monthly}**\n";
        $response .= "• 7 hari terakhir: **{$weekly}**\n\n";

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
        $weeklyAllowance = $context['weekly_allowance'] ?? 0;
        $weeklyExpense = $context['weekly_expense'] ?? 0;

        if ($weeklyAllowance > 0 && $weeklyExpense > $weeklyAllowance) {
            $response .= "\n⚠️ Pengeluaran mingguan sudah melebihi budget!";
        } else {
            $response .= "\n✅ Keep tracking pengeluaranmu ya!";
        }

        return $response;
    }

    /**
     * Handle report pengeluaran filtered by specific category
     * 
     * @param int $userId User ID
     * @param string $category Category name to filter
     */
    protected function handleReportPengeluaranByCategory(int $userId, string $category): string
    {
        // Query pengeluaran bulan ini untuk kategori tertentu
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $startOfWeek = now()->subDays(7);

        // Get category ID
        $categoryModel = \App\Models\Category::where('name', $category)
            ->where('type', 'expense')
            ->first();

        if (!$categoryModel) {
            return "🤔 Kategori **{$category}** tidak ditemukan.\n\n" .
                "Kategori yang tersedia: Makan, Transport, Nongkrong, Akademik, Lainnya.";
        }

        // Query monthly expense for this category
        $monthlyExpense = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('category_id', $categoryModel->id)
            ->dateRange($startOfMonth, $endOfMonth)
            ->sum('amount');

        // Query weekly expense for this category
        $weeklyExpense = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('category_id', $categoryModel->id)
            ->dateRange($startOfWeek, now())
            ->sum('amount');

        // Query transaction count
        $transactionCount = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('category_id', $categoryModel->id)
            ->dateRange($startOfMonth, $endOfMonth)
            ->count();

        $monthlyFormatted = 'Rp ' . number_format($monthlyExpense, 0, ',', '.');
        $weeklyFormatted = 'Rp ' . number_format($weeklyExpense, 0, ',', '.');

        $icon = $categoryModel->icon ?? '📊';

        $response = "{$icon} **Pengeluaran {$category}:**\n\n";
        $response .= "• Bulan ini: **{$monthlyFormatted}**\n";
        $response .= "• 7 hari terakhir: **{$weeklyFormatted}**\n";
        $response .= "• Jumlah transaksi: **{$transactionCount}x**\n\n";

        // Recent transactions
        $recentTransactions = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('category_id', $categoryModel->id)
            ->orderBy('transaction_date', 'desc')
            ->limit(3)
            ->get();

        if ($recentTransactions->isNotEmpty()) {
            $response .= "**Transaksi Terakhir:**\n";
            foreach ($recentTransactions as $tx) {
                $txAmount = 'Rp ' . number_format((float) $tx->amount, 0, ',', '.');
                $txDate = $tx->transaction_date ? $tx->transaction_date->format('d M') : '-';
                $desc = $tx->description ? ": {$tx->description}" : '';
                $response .= "• {$txDate} - {$txAmount}{$desc}\n";
            }
        }

        // Insight
        if ($monthlyExpense == 0) {
            $response .= "\n✨ Belum ada pengeluaran {$category} bulan ini!";
        } elseif ($transactionCount > 10) {
            $response .= "\n💡 Lumayan sering belanja {$category} ya! Coba track terus pengeluaranmu.";
        } else {
            $response .= "\n✅ Keep tracking pengeluaran {$category} kamu!";
        }

        return $response;
    }

    /**
     * Handle report pengeluaran filtered by description keyword
     * 
     * Digunakan saat user mencari pengeluaran dengan kata kunci non-kategori
     * Contoh: "pengeluaran topup", "pengeluaran genshin", "pengeluaran steam"
     * 
     * @param int $userId User ID
     * @param string $searchKeyword Keyword untuk search di description
     */
    protected function handleReportPengeluaranByDescription(int $userId, string $searchKeyword): string
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $startOfWeek = now()->subDays(7);

        // Query transactions dengan description LIKE searchKeyword (case-insensitive)
        $monthlyTransactions = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('description', 'LIKE', '%' . $searchKeyword . '%')
            ->dateRange($startOfMonth, $endOfMonth)
            ->get();

        $weeklyTransactions = \App\Models\Transaction::forUser($userId)
            ->expense()
            ->where('description', 'LIKE', '%' . $searchKeyword . '%')
            ->dateRange($startOfWeek, now())
            ->get();

        $monthlyExpense = $monthlyTransactions->sum('amount');
        $weeklyExpense = $weeklyTransactions->sum('amount');
        $transactionCount = $monthlyTransactions->count();

        // Jika tidak ada transaksi ditemukan
        if ($transactionCount === 0) {
            return "🔍 **Pencarian: '{$searchKeyword}'**\n\n" .
                "Belum ada pengeluaran dengan kata kunci **\"{$searchKeyword}\"** bulan ini.\n\n" .
                "💡 Tips: Pastikan kata kunci sesuai dengan deskripsi transaksi yang kamu catat.\n" .
                "Contoh: \"topup gopay\", \"beli steam wallet\", dll.";
        }

        $monthlyFormatted = 'Rp ' . number_format($monthlyExpense, 0, ',', '.');
        $weeklyFormatted = 'Rp ' . number_format($weeklyExpense, 0, ',', '.');

        $response = "🔍 **Pengeluaran '{$searchKeyword}':**\n\n";
        $response .= "• Bulan ini: **{$monthlyFormatted}**\n";
        $response .= "• 7 hari terakhir: **{$weeklyFormatted}**\n";
        $response .= "• Ditemukan: **{$transactionCount} transaksi**\n\n";

        // Tampilkan detail transaksi (max 5)
        $recentTransactions = $monthlyTransactions->sortByDesc('transaction_date')->take(5);

        if ($recentTransactions->isNotEmpty()) {
            $response .= "**Detail Transaksi:**\n";
            foreach ($recentTransactions as $tx) {
                $txAmount = 'Rp ' . number_format((float) $tx->amount, 0, ',', '.');
                $txDate = $tx->transaction_date ? $tx->transaction_date->format('d M') : '-';
                $desc = $tx->description ?? '-';
                $categoryName = $tx->category ? $tx->category->name : 'Lainnya';
                $response .= "• {$txDate} - {$txAmount} ({$categoryName})\n";
                $response .= "  _{$desc}_\n";
            }
        }

        // Insight
        if ($transactionCount >= 5) {
            $response .= "\n💡 Kamu cukup sering spending untuk '{$searchKeyword}' ya!";
        } else {
            $response .= "\n✅ Keep tracking pengeluaranmu!";
        }

        return $response;
    }

    /**
     * Handle report pemasukan intent
     */
    protected function handleReportPemasukan(array $context): string
    {
        $monthlyIncome = $context['monthly_income'] ?? 0;
        $monthlyExpense = $context['monthly_expense'] ?? 0;
        $net = $monthlyIncome - $monthlyExpense;

        $incomeFormatted = 'Rp ' . number_format($monthlyIncome, 0, ',', '.');
        $expenseFormatted = 'Rp ' . number_format($monthlyExpense, 0, ',', '.');
        $netFormatted = 'Rp ' . number_format(abs($net), 0, ',', '.');

        $response = "💵 **Pemasukan Bulan Ini:**\n\n";
        $response .= "Pemasukan: **{$incomeFormatted}**\n";
        $response .= "Pengeluaran: {$expenseFormatted}\n\n";

        if ($net >= 0) {
            $response .= "Sisa: **+{$netFormatted}** ✅\n\n";
            $response .= "Bagus! Kamu masih surplus bulan ini. 💪";
        } else {
            $response .= "Defisit: **-{$netFormatted}** ⚠️\n\n";
            $response .= "Hmm, pengeluaran lebih besar dari pemasukan. Evaluasi spending-nya ya!";
        }

        return $response;
    }

    /**
     * Handle report kategori intent
     */
    protected function handleReportKategori(array $context): string
    {
        $response = "📈 **Breakdown Kategori Pengeluaran:**\n\n";

        if (empty($context['category_breakdown'])) {
            $response .= "Belum ada data pengeluaran per kategori.\n";
            $response .= "Catat beberapa transaksi dulu ya! 📝";
            return $response;
        }

        $total = 0;
        foreach ($context['category_breakdown'] as $cat) {
            $total += $cat['total'];
        }

        foreach ($context['category_breakdown'] as $cat) {
            $amount = 'Rp ' . number_format($cat['total'], 0, ',', '.');
            $percentage = $total > 0 ? round(($cat['total'] / $total) * 100) : 0;
            $response .= "• **{$cat['name']}**: {$amount} ({$percentage}%)\n";
        }

        $response .= "\n📊 Total: **Rp " . number_format($total, 0, ',', '.') . "**";

        return $response;
    }

    /**
     * Handle greeting intent
     */
    protected function handleGreeting(): string
    {
        $greetings = [
            "Hai! 👋 Aku YourMoment AI Assistant. Ada yang bisa kubantu soal keuanganmu hari ini?",
            "Halo! 🌟 Senang bisa ngobrol denganmu. Mau tanya apa tentang finansialmu?",
            "Hey there! ✨ Aku siap bantu kamu manage keuangan. Ada pertanyaan?",
        ];
        return $greetings[array_rand($greetings)];
    }

    /**
     * Handle help intent
     */
    protected function handleHelp(): string
    {
        return "Aku bisa bantu kamu dengan:\n\n" .
            "💰 **Cek saldo** - Tanya saldo atau uang kamu\n" .
            "📊 **Analisis pengeluaran** - Lihat pola spending\n" .
            "💵 **Info pemasukan** - Rangkuman income\n" .
            "🎯 **Simulasi target** - Hitung berapa lama nabung\n" .
            "💡 **Tips hemat** - Rekomendasi keuangan\n" .
            "📈 **Breakdown kategori** - Detail per kategori\n\n" .
            "Contoh: \"Saldo saya dan pengeluaran\" atau \"Tips hemat dong!\"";
    }

    /**
     * Handle goal simulation request
     * Extract amounts from message and calculate REALISTIC simulation
     * Supports MULTI-TARGET and CONVERSATION STATE
     * 
     * @param string $message User's message
     * @param array $context Financial context with category breakdown
     */
    protected function handleGoalSimulation(string $message, array $context): string
    {
        // =====================================================
        // STEP 1: Extract MULTIPLE targets dari message
        // =====================================================
        $targets = GoalSimulationService::extractMultipleTargets($message);

        // =====================================================
        // STEP 2: Extract monthly/weekly allowance
        // =====================================================
        $data = $this->intentDetector->extractGoalSimulationData($message);
        $monthly = $data['monthly'];
        $weekly = $data['weekly'];

        // Get category breakdown from context
        $categoryBreakdown = $context['category_breakdown'] ?? null;

        // =====================================================
        // STEP 3: Validasi targets
        // =====================================================
        if (empty($targets)) {
            // Fallback ke single target dari extractAmounts
            if ($data['target'] !== null) {
                $targets = [
                    ['name' => 'target', 'amount' => $data['target']]
                ];
            } else {
                return "🤔 Aku butuh info nominal targetmu. Contoh:\n\n" .
                    "\"Ingin beli laptop 10jt, uang jajan 2jt/bulan, berapa lama?\"\n\n" .
                    "Kamu juga bisa sebutkan **beberapa target sekaligus**:\n" .
                    "\"laptop 7jt dan hp 4jt\"\n\n" .
                    "Coba sebutkan:\n" .
                    "• **Target:** barang apa dan berapa harganya?\n" .
                    "• **Uang jajan:** berapa penghasilan per bulan?";
            }
        }

        // =====================================================
        // STEP 4: Handle jika monthly/weekly TIDAK ditemukan
        // Save state dan tanya balik
        // =====================================================
        if ($monthly === null && $weekly === null) {
            // Save pending targets ke session
            $this->sessionMemory->savePendingGoalSimulation($targets);

            // Format target list untuk display
            $targetList = GoalSimulationService::formatTargetsForDisplay($targets);
            $totalTarget = GoalSimulationService::calculateTotalTarget($targets);
            $totalFormatted = 'Rp ' . number_format($totalTarget, 0, ',', '.');

            $response = "📝 **Target yang ingin kamu capai:**\n";
            $response .= $targetList . "\n\n";

            if (count($targets) > 1) {
                $response .= "📊 **Total:** {$totalFormatted}\n\n";
            }

            $response .= "🤔 **Berapa uang jajan atau penghasilan bulananmu?**\n\n";
            $response .= "Contoh: \"2jt\" atau \"1.5 juta per bulan\"\n\n";
            $response .= "💡 *Dengan info ini, aku bisa hitung simulasi yang realistis!*";

            return $response;
        }

        // =====================================================
        // STEP 5: Run simulation
        // =====================================================

        // Jika ada weekly, convert ke monthly
        if ($weekly !== null && $monthly === null) {
            $monthly = (int) ($weekly * 4.33);
        }

        // Run simulation based on number of targets
        if (count($targets) > 1) {
            return $this->goalSimulationService->simulateMultipleTargets($targets, $monthly, $categoryBreakdown);
        } else {
            $totalTarget = GoalSimulationService::calculateTotalTarget($targets);
            return $this->goalSimulationService->simulate($totalTarget, $monthly, $categoryBreakdown);
        }
    }

    /**
     * Handle future budget planning intent
     * 
     * @param string $message Original user message
     * @param array $meta Metadata from intent detection (category, period, periodCount, useBalance)
     * @return string Response dengan rencana budget
     */
    protected function handleFutureBudgetPlanning(string $message, array $meta): string
    {
        $user = auth()->user();

        // Get period from meta or detect from message
        $period = $meta['period'] ?? $this->intentDetector->detectFuturePeriod($message);
        $periodCount = $meta['periodCount'] ?? $this->intentDetector->extractPeriodCount($message, $period);
        $category = $meta['category'] ?? null;
        $useBalance = $meta['useBalance'] ?? $this->intentDetector->isBalanceBasedPlanning($message);

        return $this->futureBudgetService->generate(
            $user->id,
            $category,
            $period,
            $periodCount,
            $useBalance
        );
    }
}
