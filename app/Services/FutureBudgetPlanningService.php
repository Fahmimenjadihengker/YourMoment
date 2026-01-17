<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\WalletSetting;
use Illuminate\Support\Facades\DB;

/**
 * FutureBudgetPlanningService
 * 
 * Service untuk membuat rencana budget masa depan berdasarkan:
 * 1. Rata-rata pengeluaran periode sebelumnya (default)
 * 2. Saldo wallet user saat ini (jika useBalance = true)
 */
class FutureBudgetPlanningService
{
    /**
     * Persentase alokasi ideal per kategori
     */
    protected array $idealAllocation = [
        'Makan' => 40,
        'Transport' => 20,
        'Nongkrong' => 15,
        'Akademik' => 10,
        'Lainnya' => 15,
    ];

    /**
     * Generate rencana budget masa depan
     * 
     * @param int $userId User ID
     * @param string|null $category Kategori spesifik (null = semua kategori)
     * @param string $period 'minggu', 'bulan', atau 'hari'
     * @param int $periodCount Jumlah periode (default: 1)
     * @param bool $useBalance Gunakan saldo sebagai basis perhitungan
     * @return string Response text untuk AI chat
     */
    public function generate(int $userId, ?string $category = null, string $period = 'minggu', int $periodCount = 1, bool $useBalance = false): string
    {
        // Jika user minta berdasarkan saldo
        if ($useBalance) {
            return $this->generateFromBalance($userId, $period, $periodCount);
        }

        if ($category !== null) {
            return $this->generateForCategory($userId, $category, $period, $periodCount);
        }

        return $this->generateGlobal($userId, $period, $periodCount);
    }

    /**
     * Generate rencana budget berdasarkan SALDO saat ini
     * Mode "cara paling hemat dengan uang yang saya miliki"
     */
    protected function generateFromBalance(int $userId, string $period, int $periodCount): string
    {
        // Get current balance
        $walletSetting = WalletSetting::where('user_id', $userId)->first();
        $balance = $walletSetting?->balance ?? 0;

        if ($balance <= 0) {
            return $this->noBalanceResponse($period, $periodCount);
        }

        // Calculate budget based on period
        $totalDays = $this->calculateTotalDays($period, $periodCount);
        $dailyBudget = $balance / $totalDays;
        $weeklyBudget = $dailyBudget * 7;
        $monthlyBudget = $dailyBudget * 30;

        // Calculate allocation per category
        $categoryAllocations = $this->calculateCategoryAllocations($balance, $period, $periodCount);

        return $this->formatBalanceBasedResponse(
            $balance,
            $period,
            $periodCount,
            $totalDays,
            $dailyBudget,
            $weeklyBudget,
            $monthlyBudget,
            $categoryAllocations
        );
    }

    /**
     * Calculate total days untuk periode
     */
    protected function calculateTotalDays(string $period, int $periodCount): int
    {
        return match ($period) {
            'hari' => $periodCount,
            'minggu' => $periodCount * 7,
            'bulan' => $periodCount * 30,
            default => $periodCount * 7,
        };
    }

    /**
     * Calculate alokasi per kategori berdasarkan persentase ideal
     */
    protected function calculateCategoryAllocations(float $totalBudget, string $period, int $periodCount): array
    {
        $allocations = [];
        $totalDays = $this->calculateTotalDays($period, $periodCount);

        foreach ($this->idealAllocation as $categoryName => $percentage) {
            $totalAmount = ($totalBudget * $percentage) / 100;
            $dailyAmount = $totalAmount / $totalDays;
            $weeklyAmount = $dailyAmount * 7;

            $allocations[] = [
                'name' => $categoryName,
                'icon' => $this->getCategoryIcon($categoryName),
                'percentage' => $percentage,
                'total' => round($totalAmount),
                'daily' => round($dailyAmount),
                'weekly' => round($weeklyAmount),
            ];
        }

        return $allocations;
    }

    /**
     * Get icon untuk kategori
     */
    protected function getCategoryIcon(string $categoryName): string
    {
        return match ($categoryName) {
            'Makan' => '🍔',
            'Transport' => '🚗',
            'Nongkrong' => '☕',
            'Akademik' => '📚',
            'Lainnya' => '📦',
            default => '💰',
        };
    }

    /**
     * Format response untuk balance-based planning
     */
    protected function formatBalanceBasedResponse(
        float $balance,
        string $period,
        int $periodCount,
        int $totalDays,
        float $dailyBudget,
        float $weeklyBudget,
        float $monthlyBudget,
        array $categoryAllocations
    ): string {
        $periodLabel = $this->getPeriodLabel($period, $periodCount);
        $balanceFormatted = 'Rp ' . number_format($balance, 0, ',', '.');
        $dailyFormatted = 'Rp ' . number_format($dailyBudget, 0, ',', '.');
        $weeklyFormatted = 'Rp ' . number_format($weeklyBudget, 0, ',', '.');
        $monthlyFormatted = 'Rp ' . number_format($monthlyBudget, 0, ',', '.');

        $response = "💰 **Strategi Hemat {$periodLabel}**\n\n";

        // Tampilkan saldo saat ini
        $response .= "📊 **Saldo Kamu Saat Ini:**\n";
        $response .= "💵 **{$balanceFormatted}**\n\n";

        // Budget breakdown
        $response .= "📅 **Alokasi Budget ({$totalDays} hari):**\n";
        if ($period === 'bulan' || $totalDays >= 28) {
            $response .= "• Per bulan: **{$monthlyFormatted}**\n";
        }
        if ($totalDays >= 7) {
            $response .= "• Per minggu: **{$weeklyFormatted}**\n";
        }
        $response .= "• Per hari: **{$dailyFormatted}**\n\n";

        // Alokasi per kategori
        $response .= "🎯 **Alokasi Ideal per Kategori:**\n";
        foreach ($categoryAllocations as $alloc) {
            $totalFormatted = 'Rp ' . number_format($alloc['total'], 0, ',', '.');
            $dailyFormatted = 'Rp ' . number_format($alloc['daily'], 0, ',', '.');
            $response .= "{$alloc['icon']} **{$alloc['name']}** ({$alloc['percentage']}%): {$totalFormatted}";
            $response .= " (~{$dailyFormatted}/hari)\n";
        }

        // Strategi hemat konkret
        $response .= "\n💡 **Strategi Hemat Konkret:**\n";
        $response .= $this->generateSavingStrategies($dailyBudget, $categoryAllocations);

        // Warning jika budget ketat
        if ($dailyBudget < 30000) {
            $response .= "\n⚠️ **Perhatian:** Budget harianmu cukup ketat. ";
            $response .= "Prioritaskan kebutuhan pokok dan kurangi pengeluaran tidak penting.\n";
        }

        return $response;
    }

    /**
     * Generate strategi hemat konkret berdasarkan budget
     */
    protected function generateSavingStrategies(float $dailyBudget, array $categoryAllocations): string
    {
        $strategies = "";

        // Strategi berdasarkan budget harian
        if ($dailyBudget < 50000) {
            $strategies .= "• 🍚 **Makan:** Masak sendiri atau beli warteg (max Rp 15rb/porsi)\n";
            $strategies .= "• 🚶 **Transport:** Jalan kaki/sepeda untuk jarak dekat\n";
            $strategies .= "• ☕ **Nongkrong:** Batasi 1x per minggu, pilih tempat murah\n";
        } elseif ($dailyBudget < 100000) {
            $strategies .= "• 🍚 **Makan:** Kombinasi masak + makan luar (2:1)\n";
            $strategies .= "• 🚗 **Transport:** Gunakan transportasi umum\n";
            $strategies .= "• ☕ **Nongkrong:** Max 2x seminggu, set budget max Rp 50rb\n";
        } else {
            $strategies .= "• 🍚 **Makan:** Fleksibel, tapi tetap kontrol porsi\n";
            $strategies .= "• 🚗 **Transport:** Bisa ojol, tapi bandingkan harga\n";
            $strategies .= "• ☕ **Nongkrong:** Enjoy, tapi tetap catat pengeluaran\n";
        }

        // Tips umum
        $strategies .= "• 📝 **Tracking:** Catat setiap pengeluaran di app ini\n";
        $strategies .= "• 🎯 **Rule 24 jam:** Tunda pembelian impulsif 1 hari\n";
        $strategies .= "• 💪 **Challenge:** Coba 1 hari tanpa pengeluaran per minggu\n";

        return $strategies;
    }

    /**
     * Response ketika saldo kosong
     */
    protected function noBalanceResponse(string $period, int $periodCount): string
    {
        $periodLabel = $this->getPeriodLabel($period, $periodCount);

        $response = "💰 **Strategi Hemat {$periodLabel}**\n\n";
        $response .= "⚠️ **Saldo kamu saat ini: Rp 0**\n\n";
        $response .= "Untuk membuat rencana budget, kamu perlu:\n";
        $response .= "1. Update saldo di **Pengaturan Wallet**\n";
        $response .= "2. Atau catat pemasukan terlebih dahulu\n\n";
        $response .= "💡 Setelah ada saldo, aku bisa buatkan strategi hemat yang detail!";

        return $response;
    }

    /**
     * Generate rencana budget untuk kategori spesifik
     */
    protected function generateForCategory(int $userId, string $category, string $period, int $periodCount): string
    {
        // Get category from database
        $categoryModel = Category::where('name', $category)
            ->where('type', 'expense')
            ->first();

        if (!$categoryModel) {
            return $this->generateGlobal($userId, $period, $periodCount);
        }

        // Calculate average spending for this category
        $averageData = $this->calculateCategoryAverage($userId, $categoryModel->id, $period);

        if ($averageData['average'] == 0) {
            return $this->noDataResponse($category, $period, $periodCount);
        }

        // Calculate recommended budget (average + 10% buffer)
        $recommendedBudget = $averageData['average'] * 1.1;
        $totalBudget = $recommendedBudget * $periodCount;

        // Generate response
        return $this->formatCategoryResponse(
            $category,
            $categoryModel->icon ?? '📊',
            $period,
            $periodCount,
            $averageData,
            $recommendedBudget,
            $totalBudget
        );
    }

    /**
     * Generate rencana budget global (semua kategori)
     */
    protected function generateGlobal(int $userId, string $period, int $periodCount): string
    {
        // Get all expense categories with their averages
        $categories = Category::where('type', 'expense')->get();
        $categoryBudgets = [];
        $totalAverage = 0;

        foreach ($categories as $cat) {
            $avgData = $this->calculateCategoryAverage($userId, $cat->id, $period);
            if ($avgData['average'] > 0) {
                $recommended = $avgData['average'] * 1.1;
                $categoryBudgets[] = [
                    'name' => $cat->name,
                    'icon' => $cat->icon ?? '📦',
                    'average' => $avgData['average'],
                    'recommended' => $recommended,
                    'transaction_count' => $avgData['transaction_count'],
                ];
                $totalAverage += $recommended;
            }
        }

        if (empty($categoryBudgets)) {
            return $this->noDataResponse(null, $period, $periodCount);
        }

        // Sort by recommended (highest first)
        usort($categoryBudgets, fn($a, $b) => $b['recommended'] <=> $a['recommended']);

        return $this->formatGlobalResponse($period, $periodCount, $categoryBudgets, $totalAverage);
    }

    /**
     * Calculate average spending for a category
     * 
     * @param int $userId
     * @param int $categoryId
     * @param string $period 'minggu', 'bulan', atau 'hari'
     * @return array ['average' => float, 'total' => float, 'periods' => int, 'transaction_count' => int]
     */
    protected function calculateCategoryAverage(int $userId, int $categoryId, string $period): array
    {
        // Determine date range based on period (look back 4 periods)
        $lookbackPeriods = 4;
        $endDate = now();

        switch ($period) {
            case 'bulan':
                $startDate = now()->subMonths($lookbackPeriods);
                break;
            case 'hari':
                $startDate = now()->subDays($lookbackPeriods * 7); // Look back 4 weeks for daily
                $lookbackPeriods = 28; // days
                break;
            case 'minggu':
            default:
                $startDate = now()->subWeeks($lookbackPeriods);
                break;
        }

        // Get total spending
        $result = Transaction::forUser($userId)
            ->expense()
            ->where('category_id', $categoryId)
            ->dateRange($startDate, $endDate)
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->first();

        $total = (float) ($result->total ?? 0);
        $count = (int) ($result->count ?? 0);

        // Calculate average per period
        $average = $lookbackPeriods > 0 ? $total / $lookbackPeriods : 0;

        return [
            'average' => round($average),
            'total' => $total,
            'periods' => $lookbackPeriods,
            'transaction_count' => $count,
        ];
    }

    /**
     * Format response untuk kategori spesifik
     */
    protected function formatCategoryResponse(
        string $category,
        string $icon,
        string $period,
        int $periodCount,
        array $averageData,
        float $recommendedBudget,
        float $totalBudget
    ): string {
        $periodLabel = $this->getPeriodLabel($period, $periodCount);
        $periodSingular = $this->getPeriodLabel($period, 1);

        $avgFormatted = 'Rp ' . number_format($averageData['average'], 0, ',', '.');
        $recommendedFormatted = 'Rp ' . number_format($recommendedBudget, 0, ',', '.');
        $totalFormatted = 'Rp ' . number_format($totalBudget, 0, ',', '.');

        $response = "{$icon} **Rencana Budget {$category} - {$periodLabel}**\n\n";

        $response .= "📈 **Analisis Pengeluaran Sebelumnya:**\n";
        $response .= "• Rata-rata per {$periodSingular}: {$avgFormatted}\n";
        $response .= "• Total transaksi: {$averageData['transaction_count']}x\n\n";

        $response .= "💰 **Rekomendasi Budget:**\n";
        $response .= "• Per {$periodSingular}: **{$recommendedFormatted}**\n";

        if ($periodCount > 1) {
            $response .= "• Total {$periodLabel}: **{$totalFormatted}**\n";
        }

        // Add daily breakdown for weekly/monthly
        if ($period === 'minggu') {
            $dailyBudget = $recommendedBudget / 7;
            $dailyFormatted = 'Rp ' . number_format($dailyBudget, 0, ',', '.');
            $response .= "• Per hari: ~{$dailyFormatted}\n";
        } elseif ($period === 'bulan') {
            $dailyBudget = $recommendedBudget / 30;
            $weeklyBudget = $recommendedBudget / 4;
            $dailyFormatted = 'Rp ' . number_format($dailyBudget, 0, ',', '.');
            $weeklyFormatted = 'Rp ' . number_format($weeklyBudget, 0, ',', '.');
            $response .= "• Per minggu: ~{$weeklyFormatted}\n";
            $response .= "• Per hari: ~{$dailyFormatted}\n";
        }

        $response .= "\n💡 **Tips:**\n";
        $response .= $this->generateTips($category, $recommendedBudget, $averageData['average']);

        return $response;
    }

    /**
     * Format response untuk budget global (semua kategori)
     */
    protected function formatGlobalResponse(
        string $period,
        int $periodCount,
        array $categoryBudgets,
        float $totalRecommended
    ): string {
        $periodLabel = $this->getPeriodLabel($period, $periodCount);
        $periodSingular = $this->getPeriodLabel($period, 1);

        $totalFormatted = 'Rp ' . number_format($totalRecommended * $periodCount, 0, ',', '.');
        $perPeriodFormatted = 'Rp ' . number_format($totalRecommended, 0, ',', '.');

        $response = "📊 **Rencana Budget {$periodLabel}**\n\n";

        $response .= "💰 **Rekomendasi per Kategori:**\n";

        foreach ($categoryBudgets as $cat) {
            $budgetFormatted = 'Rp ' . number_format($cat['recommended'], 0, ',', '.');
            $response .= "{$cat['icon']} **{$cat['name']}**: {$budgetFormatted}\n";
        }

        $response .= "\n📈 **Total Budget:**\n";
        $response .= "• Per {$periodSingular}: **{$perPeriodFormatted}**\n";

        if ($periodCount > 1) {
            $response .= "• Total {$periodLabel}: **{$totalFormatted}**\n";
        }

        // Add daily breakdown
        if ($period === 'minggu') {
            $dailyBudget = $totalRecommended / 7;
            $dailyFormatted = 'Rp ' . number_format($dailyBudget, 0, ',', '.');
            $response .= "• Per hari: ~{$dailyFormatted}\n";
        } elseif ($period === 'bulan') {
            $weeklyBudget = $totalRecommended / 4;
            $dailyBudget = $totalRecommended / 30;
            $weeklyFormatted = 'Rp ' . number_format($weeklyBudget, 0, ',', '.');
            $dailyFormatted = 'Rp ' . number_format($dailyBudget, 0, ',', '.');
            $response .= "• Per minggu: ~{$weeklyFormatted}\n";
            $response .= "• Per hari: ~{$dailyFormatted}\n";
        }

        $response .= "\n💡 **Tips Umum:**\n";
        $response .= "• Prioritaskan kebutuhan pokok (makan) sebelum keinginan\n";
        $response .= "• Sisihkan 10-20% untuk tabungan/darurat\n";
        $response .= "• Track pengeluaran harian agar tetap on budget\n";

        return $response;
    }

    /**
     * Response ketika tidak ada data historis
     */
    protected function noDataResponse(?string $category, string $period, int $periodCount): string
    {
        $periodLabel = $this->getPeriodLabel($period, $periodCount);
        $categoryText = $category ? " untuk **{$category}**" : "";

        $response = "📊 **Rencana Budget{$categoryText} - {$periodLabel}**\n\n";
        $response .= "⚠️ Belum ada data pengeluaran sebelumnya{$categoryText}.\n\n";
        $response .= "💡 **Saran:**\n";
        $response .= "• Catat beberapa transaksi dulu agar aku bisa menganalisis pola pengeluaranmu\n";
        $response .= "• Setelah ada data, aku akan buatkan rekomendasi budget yang lebih akurat\n\n";

        if ($category === 'Makan' || $category === null) {
            $response .= "📝 **Patokan Umum Budget Makan:**\n";
            $response .= "• Mahasiswa: Rp 20.000 - 35.000/hari\n";
            $response .= "• Per minggu: ~Rp 150.000 - 250.000\n";
        }

        return $response;
    }

    /**
     * Generate tips berdasarkan kategori dan budget
     */
    protected function generateTips(string $category, float $recommended, float $average): string
    {
        $tips = "";

        switch ($category) {
            case 'Makan':
                $tips .= "• Bawa bekal dari rumah 2-3x seminggu untuk hemat\n";
                $tips .= "• Manfaatkan promo aplikasi pesan makanan\n";
                $tips .= "• Masak sendiri di weekend untuk meal prep\n";
                break;
            case 'Transport':
                $tips .= "• Gunakan transportasi umum jika memungkinkan\n";
                $tips .= "• Manfaatkan promo ojol di jam tertentu\n";
                $tips .= "• Pertimbangkan jalan kaki untuk jarak dekat\n";
                break;
            case 'Nongkrong':
                $tips .= "• Batasi nongkrong 1-2x seminggu\n";
                $tips .= "• Pilih tempat dengan harga terjangkau\n";
                $tips .= "• Ajak teman buat patungan\n";
                break;
            case 'Akademik':
                $tips .= "• Cari buku bekas atau PDF legal\n";
                $tips .= "• Manfaatkan perpustakaan kampus\n";
                $tips .= "• Patungan fotokopi dengan teman\n";
                break;
            default:
                $tips .= "• Track setiap pengeluaran\n";
                $tips .= "• Tunda pembelian impulsif 24 jam\n";
                $tips .= "• Review budget mingguan\n";
        }

        return $tips;
    }

    /**
     * Get period label in Indonesian
     */
    protected function getPeriodLabel(string $period, int $count): string
    {
        $labels = [
            'hari' => $count === 1 ? 'Hari Depan' : "{$count} Hari ke Depan",
            'minggu' => $count === 1 ? 'Minggu Depan' : "{$count} Minggu ke Depan",
            'bulan' => $count === 1 ? 'Bulan Depan' : "{$count} Bulan ke Depan",
        ];

        return $labels[$period] ?? 'Minggu Depan';
    }
}
