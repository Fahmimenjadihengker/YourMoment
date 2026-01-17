<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

/**
 * FinancialAnalysisService - Analisis Keuangan Berbasis Periode
 * 
 * KONSEP UTAMA:
 * - Analisis BERBASIS pemasukan & pengeluaran DALAM PERIODE
 * - Saldo total HANYA untuk visual reference, TIDAK dipakai dalam analisis
 * - Mendukung 3 mode: monthly, weekly, simulation
 * 
 * FORMULA REKOMENDASI:
 * - Sisa Dana Periode = Pemasukan Periode - Pengeluaran Sudah Terjadi
 * - Rekomendasi Harian = Sisa Dana Periode / Sisa Hari
 */
class FinancialAnalysisService
{
    protected FinancialSummaryService $summaryService;
    protected FinancialHealthService $healthService;

    public function __construct(
        FinancialSummaryService $summaryService,
        FinancialHealthService $healthService
    ) {
        $this->summaryService = $summaryService;
        $this->healthService = $healthService;
    }

    /**
     * Generate analisis untuk mode WEEKLY atau MONTHLY
     * Data diambil dari database melalui FinancialSummaryService
     */
    public function generateAnalysis(int $userId, string $periodMode = 'monthly'): array
    {
        $now = Carbon::now();
        
        // Tentukan periode berdasarkan mode
        if ($periodMode === 'weekly') {
            $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $now->copy()->endOfDay();
            $periodEndDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
            $periodLabel = 'Minggu Ini';
            $periodDetail = $startDate->format('d M') . ' - ' . $periodEndDate->format('d M Y');
            $totalDays = 7;
            $daysElapsed = $startDate->diffInDays($now) + 1;
            $daysRemaining = max(0, 7 - $daysElapsed);
            
            $prevStartDate = $startDate->copy()->subWeek();
            $prevEndDate = $prevStartDate->copy()->addDays(6)->endOfDay();
            $comparisonLabel = 'Minggu Lalu';
        } else {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfDay();
            $periodEndDate = $now->copy()->endOfMonth();
            $periodLabel = $now->translatedFormat('F Y');
            $periodDetail = 'Bulan ' . $periodLabel;
            $periodMode = 'monthly';
            $totalDays = $now->daysInMonth;
            $daysElapsed = $now->day;
            $daysRemaining = max(0, $totalDays - $daysElapsed);
            
            $prevStartDate = $startDate->copy()->subMonth();
            $prevEndDate = $prevStartDate->copy()->endOfMonth();
            $comparisonLabel = $prevStartDate->translatedFormat('F Y');
        }

        // Ambil data dari FinancialSummaryService
        $currentSummary = $this->summaryService->getSummary($userId, $startDate, $endDate);
        $previousSummary = $this->summaryService->getSummary($userId, $prevStartDate, $prevEndDate);
        
        // Saldo total HANYA untuk visual reference
        $totalBalance = $this->summaryService->getTotalBalance($userId);
        
        // Top categories
        $topCategories = $this->summaryService->getTopExpenseCategories($userId, $startDate, $endDate, 5);

        // Generate analisis menggunakan data periode
        return $this->processAnalysis(
            periodMode: $periodMode,
            periodLabel: $periodLabel,
            periodDetail: $periodDetail,
            totalDays: $totalDays,
            daysElapsed: $daysElapsed,
            daysRemaining: $daysRemaining,
            periodIncome: $currentSummary['total_income'],
            periodExpense: $currentSummary['total_expense'],
            transactionCount: $currentSummary['transaction_count'],
            previousSummary: $previousSummary,
            comparisonLabel: $comparisonLabel,
            topCategories: $topCategories,
            totalBalance: $totalBalance,
            userId: $userId,
            startDate: $startDate,
            endDate: $endDate,
            isSimulation: false
        );
    }

    /**
     * Generate analisis untuk mode SIMULASI
     * Data di-input manual, TIDAK menyentuh database
     */
    public function generateSimulationAnalysis(array $input): array
    {
        $periodIncome = (float) ($input['income'] ?? 0);
        $periodExpense = (float) ($input['expense'] ?? 0);
        $daysRemaining = (int) ($input['days_remaining'] ?? 1);
        $totalDays = (int) ($input['total_days'] ?? 30);
        $daysElapsed = max(1, $totalDays - $daysRemaining);
        
        // Generate analisis menggunakan data simulasi
        return $this->processAnalysis(
            periodMode: 'simulation',
            periodLabel: 'Simulasi',
            periodDetail: 'Hasil Simulasi Custom',
            totalDays: $totalDays,
            daysElapsed: $daysElapsed,
            daysRemaining: $daysRemaining,
            periodIncome: $periodIncome,
            periodExpense: $periodExpense,
            transactionCount: 0,
            previousSummary: null,
            comparisonLabel: null,
            topCategories: collect([]),
            totalBalance: null, // Tidak ada saldo total di simulasi
            userId: null,
            startDate: null,
            endDate: null,
            isSimulation: true
        );
    }

    /**
     * Core processing untuk semua mode
     * LOGIKA UTAMA: Berbasis pemasukan & pengeluaran PERIODE
     */
    private function processAnalysis(
        string $periodMode,
        string $periodLabel,
        string $periodDetail,
        int $totalDays,
        int $daysElapsed,
        int $daysRemaining,
        float $periodIncome,
        float $periodExpense,
        int $transactionCount,
        ?array $previousSummary,
        ?string $comparisonLabel,
        $topCategories,
        ?float $totalBalance,
        ?int $userId,
        ?Carbon $startDate,
        ?Carbon $endDate,
        bool $isSimulation
    ): array {
        
        // === CORE CALCULATION: Berbasis PERIODE ===
        // Sisa dana = Pemasukan periode - Pengeluaran yang sudah terjadi
        $remainingBudget = max(0, $periodIncome - $periodExpense);
        
        // Rasio pengeluaran terhadap pemasukan
        $expenseRatio = $periodIncome > 0 
            ? round(($periodExpense / $periodIncome) * 100, 1) 
            : ($periodExpense > 0 ? 100 : 0);
        
        // Rasio waktu yang sudah berlalu
        $timeRatio = $totalDays > 0 
            ? round(($daysElapsed / $totalDays) * 100, 1) 
            : 0;
        
        // === REKOMENDASI HARIAN ===
        $dailyRecommendation = $this->calculatePeriodBasedRecommendation(
            periodIncome: $periodIncome,
            periodExpense: $periodExpense,
            daysRemaining: $daysRemaining,
            daysElapsed: $daysElapsed,
            topCategories: $topCategories,
            expenseRatio: $expenseRatio,
            timeRatio: $timeRatio
        );
        
        // === ANALISIS POLA HARIAN (hanya untuk mode non-simulasi) ===
        $dailyPatterns = [];
        if (!$isSimulation && $userId && $startDate && $endDate) {
            $dailyPatterns = $this->analyzeDailyPatterns($userId, $startDate, $endDate, $daysElapsed);
        } else {
            $dailyPatterns = [
                'average_daily' => $daysElapsed > 0 ? round($periodExpense / $daysElapsed, 0) : 0,
                'spending_consistency' => 'simulation',
                'max_daily' => 0,
                'min_daily' => 0,
                'max_day' => null,
                'has_unusual_spike' => false,
            ];
        }
        
        // === TREND (hanya untuk mode non-simulasi) ===
        $trend = $this->calculateTrend(
            ['total_income' => $periodIncome, 'total_expense' => $periodExpense, 'balance' => $periodIncome - $periodExpense, 'transaction_count' => $transactionCount],
            $previousSummary ?? ['total_income' => 0, 'total_expense' => 0, 'balance' => 0, 'transaction_count' => 0]
        );
        
        // === KOMPOSISI PENGELUARAN ===
        $composition = $this->analyzeComposition($topCategories, $periodExpense);
        
        // === POLA KEUANGAN ===
        $patterns = $this->analyzePatterns(
            periodIncome: $periodIncome,
            periodExpense: $periodExpense,
            expenseRatio: $expenseRatio,
            timeRatio: $timeRatio,
            composition: $composition,
            dailyPatterns: $dailyPatterns,
            previousSummary: $previousSummary
        );
        
        // === SKOR KESEHATAN (BERBASIS PERIODE) ===
        $healthScore = $this->calculatePeriodHealthScore(
            periodIncome: $periodIncome,
            periodExpense: $periodExpense,
            expenseRatio: $expenseRatio,
            timeRatio: $timeRatio,
            daysRemaining: $daysRemaining,
            remainingBudget: $remainingBudget,
            isSimulation: $isSimulation
        );
        
        // === WARNINGS ===
        $warnings = $this->generateWarnings(
            patterns: $patterns,
            dailyRecommendation: $dailyRecommendation,
            healthScore: $healthScore,
            expenseRatio: $expenseRatio,
            timeRatio: $timeRatio,
            isSimulation: $isSimulation
        );
        
        // === INSIGHTS ===
        $insights = $this->generateInsights(
            patterns: $patterns,
            healthScore: $healthScore,
            dailyRecommendation: $dailyRecommendation,
            expenseRatio: $expenseRatio,
            timeRatio: $timeRatio,
            periodMode: $periodMode,
            isSimulation: $isSimulation
        );

        // === FORMAT TOP CATEGORIES ===
        $formattedCategories = [];
        if ($topCategories && $topCategories->count() > 0) {
            $formattedCategories = $topCategories->map(function ($cat) use ($periodExpense) {
                $percentage = $periodExpense > 0 
                    ? round(($cat->total / $periodExpense) * 100, 1)
                    : 0;
                return [
                    'name' => $cat->name,
                    'icon' => $cat->icon ?? '📌',
                    'color' => $cat->color ?? '#6b7280',
                    'total' => (float) $cat->total,
                    'count' => (int) $cat->count,
                    'percentage' => $percentage,
                ];
            })->values()->toArray();
        }

        return [
            // Mode & Period info
            'period' => [
                'mode' => $periodMode,
                'label' => $periodLabel,
                'detail' => $periodDetail,
                'total_days' => $totalDays,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
            ],
            'comparison_period' => $comparisonLabel ? ['label' => $comparisonLabel] : null,
            'is_simulation' => $isSimulation,
            
            // Core Financial Data (BERBASIS PERIODE)
            'current' => [
                'total_income' => $periodIncome,
                'total_expense' => $periodExpense,
                'balance' => $periodIncome - $periodExpense,
                'remaining_budget' => $remainingBudget,
                'transaction_count' => $transactionCount,
            ],
            'previous' => $previousSummary,
            
            // Ratios (PENTING untuk analisis)
            'expense_ratio' => $expenseRatio,
            'time_ratio' => $timeRatio,
            
            // Saldo total (VISUAL ONLY - tidak dipakai dalam analisis)
            'total_balance' => $totalBalance,
            'total_balance_note' => 'Saldo total hanya untuk referensi visual, tidak memengaruhi analisis periode.',
            
            // Daily analysis
            'daily_patterns' => $dailyPatterns,
            'daily_recommendation' => $dailyRecommendation,
            
            // Analysis results
            'trend' => $trend,
            'composition' => $composition,
            'patterns' => $patterns,
            
            // Warnings & insights
            'warnings' => $warnings,
            'insights' => $insights,
            
            // Health score
            'health_score' => $healthScore,
            
            // Categories
            'top_categories' => $formattedCategories,
        ];
    }

    /**
     * Hitung rekomendasi harian BERBASIS PERIODE
     * Formula: Sisa Dana Periode / Sisa Hari
     */
    private function calculatePeriodBasedRecommendation(
        float $periodIncome,
        float $periodExpense,
        int $daysRemaining,
        int $daysElapsed,
        $topCategories,
        float $expenseRatio,
        float $timeRatio
    ): array {
        // Sisa dana periode = Pemasukan - Pengeluaran yang sudah terjadi
        $remainingBudget = max(0, $periodIncome - $periodExpense);
        
        // Rekomendasi harian = Sisa dana / Sisa hari
        $recommendedDaily = 0;
        if ($daysRemaining > 0) {
            $recommendedDaily = round($remainingBudget / $daysRemaining, 0);
        } elseif ($daysRemaining === 0) {
            // Hari terakhir periode
            $recommendedDaily = $remainingBudget;
        }
        
        // Rata-rata pengeluaran harian saat ini
        $currentDailyAvg = $daysElapsed > 0 ? round($periodExpense / $daysElapsed, 0) : 0;
        
        // Ideal daily (jika merata sejak awal)
        $totalDays = $daysElapsed + $daysRemaining;
        $idealDaily = $totalDays > 0 ? round($periodIncome / $totalDays, 0) : 0;
        
        // Status berdasarkan perbandingan spending speed vs time
        // Jika expense_ratio > time_ratio, berarti spending terlalu cepat
        $spendingSpeed = $timeRatio > 0 ? $expenseRatio / $timeRatio : 0;
        
        if ($periodIncome <= 0) {
            $status = 'no_income';
            $statusLabel = 'Tidak ada pemasukan';
        } elseif ($expenseRatio >= 100) {
            $status = 'exceeded';
            $statusLabel = 'Melebihi pemasukan';
        } elseif ($spendingSpeed > 1.3) {
            $status = 'over_budget';
            $statusLabel = 'Pengeluaran terlalu cepat';
        } elseif ($spendingSpeed > 1.0) {
            $status = 'warning';
            $statusLabel = 'Perlu perhatian';
        } elseif ($spendingSpeed >= 0.7) {
            $status = 'on_track';
            $statusLabel = 'Sesuai target';
        } else {
            $status = 'under_budget';
            $statusLabel = 'Di bawah batas';
        }
        
        // === REKOMENDASI PER KATEGORI ===
        $categoryRecommendations = [];
        if ($topCategories && $topCategories->count() > 0) {
            $totalCategoryPercent = 0;
            
            foreach ($topCategories as $cat) {
                $catPercent = $periodExpense > 0 
                    ? ($cat->total / $periodExpense) * 100 
                    : 0;
                $totalCategoryPercent += $catPercent;
                
                // Alokasi proporsional dari rekomendasi harian
                $catDailyRec = round(($catPercent / 100) * $recommendedDaily, 0);
                
                // Rata-rata harian aktual
                $catDailyActual = $daysElapsed > 0 ? round($cat->total / $daysElapsed, 0) : 0;
                
                // Status kategori
                if ($catDailyRec <= 0) {
                    $catStatus = 'ok';
                } elseif ($catDailyActual <= $catDailyRec * 0.8) {
                    $catStatus = 'under';
                } elseif ($catDailyActual <= $catDailyRec * 1.2) {
                    $catStatus = 'ok';
                } else {
                    $catStatus = 'over';
                }
                
                $categoryRecommendations[] = [
                    'name' => $cat->name,
                    'icon' => $cat->icon ?? '📌',
                    'color' => $cat->color ?? '#6b7280',
                    'percentage' => round($catPercent, 1),
                    'recommended_daily' => $catDailyRec,
                    'actual_daily' => $catDailyActual,
                    'status' => $catStatus,
                ];
            }
        }
        
        // Proyeksi akhir periode
        $projectedTotalExpense = $periodExpense + ($currentDailyAvg * $daysRemaining);
        $projectedEndBalance = $periodIncome - $projectedTotalExpense;
        
        // Hari sampai dana habis
        $daysUntilZero = null;
        if ($remainingBudget > 0 && $currentDailyAvg > 0) {
            $daysUntilZero = floor($remainingBudget / $currentDailyAvg);
        }

        return [
            'recommended_daily' => $recommendedDaily,
            'current_daily_avg' => $currentDailyAvg,
            'ideal_daily' => $idealDaily,
            'remaining_budget' => $remainingBudget,
            'days_remaining' => $daysRemaining,
            'status' => $status,
            'status_label' => $statusLabel,
            'spending_speed' => round($spendingSpeed, 2),
            'category_recommendations' => $categoryRecommendations,
            'projected_total_expense' => round($projectedTotalExpense, 0),
            'projected_end_balance' => round($projectedEndBalance, 0),
            'days_until_zero' => $daysUntilZero,
        ];
    }

    /**
     * Hitung skor kesehatan BERBASIS PERIODE
     */
    private function calculatePeriodHealthScore(
        float $periodIncome,
        float $periodExpense,
        float $expenseRatio,
        float $timeRatio,
        int $daysRemaining,
        float $remainingBudget,
        bool $isSimulation
    ): array {
        $score = 100;
        $breakdown = [];
        
        // === KOMPONEN 1: Rasio pengeluaran (max 40 poin) ===
        // Ideal: expense <= 70% dari income
        $ratioScore = 40;
        if ($periodIncome <= 0) {
            $ratioScore = $periodExpense > 0 ? 0 : 20; // Tidak ada income = tidak bisa dinilai
            $ratioDetail = 'Tidak ada pemasukan tercatat';
        } elseif ($expenseRatio <= 50) {
            $ratioScore = 40;
            $ratioDetail = 'Rasio pengeluaran sangat sehat';
        } elseif ($expenseRatio <= 70) {
            $ratioScore = 35;
            $ratioDetail = 'Rasio pengeluaran baik';
        } elseif ($expenseRatio <= 85) {
            $ratioScore = 25;
            $ratioDetail = 'Rasio pengeluaran cukup tinggi';
        } elseif ($expenseRatio <= 100) {
            $ratioScore = 15;
            $ratioDetail = 'Rasio pengeluaran tinggi';
        } else {
            $ratioScore = 0;
            $ratioDetail = 'Pengeluaran melebihi pemasukan';
        }
        
        $breakdown['expense_ratio'] = [
            'score' => $ratioScore,
            'max' => 40,
            'description' => 'Rasio Pengeluaran',
            'detail' => $ratioDetail . ' (' . $expenseRatio . '%)',
        ];
        
        // === KOMPONEN 2: Kecepatan pengeluaran (max 35 poin) ===
        // Apakah pengeluaran sesuai dengan waktu yang berlalu?
        $speedScore = 35;
        $spendingSpeed = $timeRatio > 0 ? $expenseRatio / $timeRatio : 0;
        
        if ($timeRatio <= 0 || $periodIncome <= 0) {
            $speedScore = 20;
            $speedDetail = 'Belum cukup data';
        } elseif ($spendingSpeed <= 0.7) {
            $speedScore = 35;
            $speedDetail = 'Pengeluaran sangat terkontrol';
        } elseif ($spendingSpeed <= 1.0) {
            $speedScore = 30;
            $speedDetail = 'Pengeluaran sesuai waktu';
        } elseif ($spendingSpeed <= 1.3) {
            $speedScore = 20;
            $speedDetail = 'Pengeluaran sedikit lebih cepat';
        } elseif ($spendingSpeed <= 1.5) {
            $speedScore = 10;
            $speedDetail = 'Pengeluaran terlalu cepat';
        } else {
            $speedScore = 0;
            $speedDetail = 'Pengeluaran sangat cepat';
        }
        
        $breakdown['spending_speed'] = [
            'score' => $speedScore,
            'max' => 35,
            'description' => 'Kecepatan Pengeluaran',
            'detail' => $speedDetail,
        ];
        
        // === KOMPONEN 3: Sisa anggaran (max 25 poin) ===
        $budgetScore = 25;
        $budgetRatio = $periodIncome > 0 ? ($remainingBudget / $periodIncome) * 100 : 0;
        
        if ($periodIncome <= 0) {
            $budgetScore = 10;
            $budgetDetail = 'Tidak ada basis perhitungan';
        } elseif ($remainingBudget <= 0) {
            $budgetScore = 0;
            $budgetDetail = 'Anggaran sudah habis';
        } elseif ($budgetRatio >= 50) {
            $budgetScore = 25;
            $budgetDetail = 'Sisa anggaran sangat baik';
        } elseif ($budgetRatio >= 30) {
            $budgetScore = 20;
            $budgetDetail = 'Sisa anggaran cukup';
        } elseif ($budgetRatio >= 15) {
            $budgetScore = 12;
            $budgetDetail = 'Sisa anggaran menipis';
        } else {
            $budgetScore = 5;
            $budgetDetail = 'Sisa anggaran kritis';
        }
        
        $breakdown['remaining_budget'] = [
            'score' => $budgetScore,
            'max' => 25,
            'description' => 'Sisa Anggaran',
            'detail' => $budgetDetail . ' (' . round($budgetRatio, 1) . '%)',
        ];
        
        // === TOTAL SCORE ===
        $score = $ratioScore + $speedScore + $budgetScore;
        
        // Tentukan label dan warna
        if ($score >= 85) {
            $label = 'Sangat Sehat';
            $color = 'green';
        } elseif ($score >= 70) {
            $label = 'Sehat';
            $color = 'green';
        } elseif ($score >= 55) {
            $label = 'Cukup';
            $color = 'yellow';
        } elseif ($score >= 40) {
            $label = 'Perlu Perhatian';
            $color = 'orange';
        } elseif ($score >= 25) {
            $label = 'Mengkhawatirkan';
            $color = 'orange';
        } else {
            $label = 'Kritis';
            $color = 'red';
        }
        
        if ($isSimulation) {
            $label .= ' (Simulasi)';
        }

        return [
            'score' => $score,
            'label' => $label,
            'color' => $color,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Analisis pola pengeluaran harian
     */
    private function analyzeDailyPatterns(int $userId, Carbon $startDate, Carbon $endDate, int $daysElapsed): array
    {
        $dailyExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($dailyExpenses->isEmpty()) {
            return [
                'average_daily' => 0,
                'max_daily' => 0,
                'min_daily' => 0,
                'max_day' => null,
                'total_days_with_expense' => 0,
                'spending_consistency' => 'no_data',
                'early_period_heavy' => false,
                'has_unusual_spike' => false,
            ];
        }

        $totals = $dailyExpenses->pluck('total')->map(fn($t) => (float) $t);
        $averageDaily = $totals->avg();
        $maxDaily = $totals->max();
        $minDaily = $totals->min();
        
        $maxDayData = $dailyExpenses->sortByDesc('total')->first();
        $maxDay = $maxDayData ? Carbon::parse($maxDayData->date)->translatedFormat('l, d M') : null;

        // Cek boros di awal periode
        $earlyPeriodDays = max(1, ceil($daysElapsed / 3));
        $earlyPeriodExpense = 0;
        $laterPeriodExpense = 0;
        
        foreach ($dailyExpenses as $day) {
            $dayNum = Carbon::parse($day->date)->diffInDays($startDate) + 1;
            if ($dayNum <= $earlyPeriodDays) {
                $earlyPeriodExpense += $day->total;
            } else {
                $laterPeriodExpense += $day->total;
            }
        }
        
        $earlyPeriodHeavy = false;
        if ($earlyPeriodExpense > 0 && $daysElapsed > $earlyPeriodDays) {
            $earlyAvg = $earlyPeriodExpense / $earlyPeriodDays;
            $laterDays = $daysElapsed - $earlyPeriodDays;
            $laterAvg = $laterDays > 0 ? $laterPeriodExpense / $laterDays : 0;
            $earlyPeriodHeavy = $laterAvg > 0 && $earlyAvg > ($laterAvg * 1.5);
        }

        // Konsistensi
        $variance = 0;
        if ($totals->count() > 1) {
            $mean = $averageDaily;
            $sumSquaredDiff = $totals->reduce(fn($carry, $value) => $carry + pow($value - $mean, 2), 0);
            $variance = $sumSquaredDiff / $totals->count();
        }
        $stdDev = sqrt($variance);
        $cv = $averageDaily > 0 ? ($stdDev / $averageDaily) : 0;
        
        $consistency = match(true) {
            $cv < 0.3 => 'stable',
            $cv < 0.6 => 'moderate',
            default => 'fluctuating',
        };

        $hasUnusualSpike = $maxDaily > ($averageDaily * 2);

        return [
            'average_daily' => round($averageDaily, 0),
            'max_daily' => round($maxDaily, 0),
            'min_daily' => round($minDaily, 0),
            'max_day' => $maxDay,
            'total_days_with_expense' => $dailyExpenses->count(),
            'spending_consistency' => $consistency,
            'early_period_heavy' => $earlyPeriodHeavy,
            'has_unusual_spike' => $hasUnusualSpike,
        ];
    }

    /**
     * Hitung trend perbandingan
     */
    private function calculateTrend(array $current, array $previous): array
    {
        $calc = fn($curr, $prev) => [
            'current' => $curr,
            'previous' => $prev,
            'change_amount' => $curr - $prev,
            'change_percent' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : ($curr > 0 ? 100 : 0),
            'direction' => $curr > $prev ? 'up' : ($curr < $prev ? 'down' : 'stable'),
        ];
        
        return [
            'income' => $calc($current['total_income'], $previous['total_income']),
            'expense' => $calc($current['total_expense'], $previous['total_expense']),
            'balance' => $calc($current['balance'], $previous['balance']),
            'has_previous_data' => ($previous['transaction_count'] ?? 0) > 0,
        ];
    }

    /**
     * Analisis komposisi pengeluaran
     */
    private function analyzeComposition($topCategories, float $totalExpense): array
    {
        if ($totalExpense <= 0 || !$topCategories || $topCategories->isEmpty()) {
            return [
                'total_categories' => 0,
                'top_category_name' => 'Tidak ada',
                'top_category_percent' => 0,
                'is_diversified' => true,
                'dominant_category' => null,
            ];
        }

        $topCat = $topCategories->first();
        $topPercent = round(($topCat->total / $totalExpense) * 100, 1);
        
        $dominantCategory = null;
        if ($topPercent > 50) {
            $dominantCategory = [
                'name' => $topCat->name,
                'percentage' => $topPercent,
                'is_excessive' => $topPercent > 60,
            ];
        }

        return [
            'total_categories' => $topCategories->count(),
            'top_category_name' => $topCat->name,
            'top_category_percent' => $topPercent,
            'is_diversified' => $topPercent < 50,
            'dominant_category' => $dominantCategory,
        ];
    }

    /**
     * Analisis pola keuangan
     */
    private function analyzePatterns(
        float $periodIncome,
        float $periodExpense,
        float $expenseRatio,
        float $timeRatio,
        array $composition,
        array $dailyPatterns,
        ?array $previousSummary
    ): array {
        $patterns = [];
        
        // Status finansial periode
        if ($periodIncome <= 0 && $periodExpense > 0) {
            $patterns['status'] = 'no_income';
            $patterns['deficit_amount'] = $periodExpense;
        } elseif ($periodExpense > $periodIncome) {
            $patterns['status'] = 'deficit';
            $patterns['deficit_amount'] = $periodExpense - $periodIncome;
            $patterns['expense_ratio'] = $expenseRatio;
        } elseif ($periodExpense == $periodIncome) {
            $patterns['status'] = 'break_even';
            $patterns['expense_ratio'] = 100;
        } else {
            $patterns['status'] = 'surplus';
            $patterns['surplus_amount'] = $periodIncome - $periodExpense;
            $patterns['expense_ratio'] = $expenseRatio;
            $patterns['saving_rate'] = round(100 - $expenseRatio, 1);
        }

        // Kecepatan pengeluaran
        $spendingSpeed = $timeRatio > 0 ? $expenseRatio / $timeRatio : 0;
        $patterns['spending_speed'] = round($spendingSpeed, 2);
        
        if ($spendingSpeed > 1.5) {
            $patterns['expense_trend'] = 'very_fast';
        } elseif ($spendingSpeed > 1.2) {
            $patterns['expense_trend'] = 'fast';
        } elseif ($spendingSpeed > 0.8) {
            $patterns['expense_trend'] = 'normal';
        } else {
            $patterns['expense_trend'] = 'slow';
        }

        // Trend dibanding periode sebelumnya
        if ($previousSummary && $previousSummary['total_expense'] > 0) {
            $expenseChange = (($periodExpense - $previousSummary['total_expense']) / $previousSummary['total_expense']) * 100;
            if ($expenseChange > 30) {
                $patterns['vs_previous'] = 'spike';
            } elseif ($expenseChange > 10) {
                $patterns['vs_previous'] = 'increasing';
            } elseif ($expenseChange < -10) {
                $patterns['vs_previous'] = 'decreasing';
            } else {
                $patterns['vs_previous'] = 'stable';
            }
        }

        // Pola harian
        $patterns['spending_consistency'] = $dailyPatterns['spending_consistency'] ?? 'no_data';
        $patterns['early_period_heavy'] = $dailyPatterns['early_period_heavy'] ?? false;
        $patterns['has_unusual_spike'] = $dailyPatterns['has_unusual_spike'] ?? false;
        
        // Kategori dominan
        $patterns['has_dominant_category'] = $composition['dominant_category'] !== null;
        if ($patterns['has_dominant_category']) {
            $patterns['dominant_category'] = $composition['dominant_category'];
        }
        $patterns['is_diversified'] = $composition['is_diversified'];

        return $patterns;
    }

    /**
     * Generate warnings
     */
    private function generateWarnings(
        array $patterns,
        array $dailyRecommendation,
        array $healthScore,
        float $expenseRatio,
        float $timeRatio,
        bool $isSimulation
    ): array {
        $warnings = [];
        $prefix = $isSimulation ? '[Simulasi] ' : '';
        
        // Warning: Tidak ada pemasukan
        if (($patterns['status'] ?? '') === 'no_income') {
            $warnings[] = [
                'level' => 'warning',
                'title' => $prefix . 'Tidak Ada Pemasukan Tercatat',
                'message' => 'Tidak ada pemasukan di periode ini, namun ada pengeluaran sebesar Rp ' . number_format($patterns['deficit_amount'] ?? 0, 0, ',', '.') . '.',
                'action' => 'Pastikan semua pemasukan sudah dicatat.',
            ];
        }
        
        // Warning: Defisit
        if (($patterns['status'] ?? '') === 'deficit') {
            $warnings[] = [
                'level' => 'critical',
                'title' => $prefix . 'Pengeluaran Melebihi Pemasukan',
                'message' => 'Pengeluaran sudah melebihi pemasukan sebesar Rp ' . number_format($patterns['deficit_amount'] ?? 0, 0, ',', '.') . '.',
                'action' => 'Kurangi pengeluaran segera untuk mencegah kondisi memburuk.',
            ];
        }
        
        // Warning: Pengeluaran terlalu cepat
        $spendingSpeed = $patterns['spending_speed'] ?? 0;
        if ($spendingSpeed > 1.3 && $expenseRatio < 100) {
            $warnings[] = [
                'level' => 'warning',
                'title' => $prefix . 'Pengeluaran Terlalu Cepat',
                'message' => 'Pengeluaranmu sudah mencapai ' . $expenseRatio . '% dari pemasukan, sementara periode baru berjalan ' . round($timeRatio, 0) . '%.',
                'action' => 'Perlu pengendalian agar keuangan tetap aman hingga akhir periode.',
            ];
        }
        
        // Warning: Proyeksi habis sebelum periode berakhir
        $daysUntilZero = $dailyRecommendation['days_until_zero'] ?? null;
        $daysRemaining = $dailyRecommendation['days_remaining'] ?? 0;
        if ($daysUntilZero !== null && $daysUntilZero < $daysRemaining && $daysUntilZero > 0) {
            $warnings[] = [
                'level' => 'critical',
                'title' => $prefix . 'Anggaran Diproyeksi Habis',
                'message' => 'Jika pola ini berlanjut, sisa anggaran akan habis dalam ' . $daysUntilZero . ' hari, sebelum periode berakhir.',
                'action' => 'Kurangi pengeluaran harian segera.',
            ];
        }
        
        // Warning: Health score rendah
        $score = $healthScore['score'] ?? 0;
        if ($score < 30) {
            $warnings[] = [
                'level' => 'critical',
                'title' => $prefix . 'Kondisi Keuangan Kritis',
                'message' => 'Skor kesehatan keuangan hanya ' . $score . '/100.',
                'action' => 'Evaluasi ulang seluruh pengeluaran dan prioritaskan kebutuhan esensial.',
            ];
        } elseif ($score < 50) {
            $warnings[] = [
                'level' => 'warning',
                'title' => $prefix . 'Kondisi Keuangan Perlu Perhatian',
                'message' => 'Skor kesehatan keuangan ' . $score . '/100 menunjukkan perlunya perbaikan.',
                'action' => 'Fokus pada pengendalian pengeluaran.',
            ];
        }
        
        // Warning: Boros di awal periode
        if ($patterns['early_period_heavy'] ?? false) {
            $warnings[] = [
                'level' => 'info',
                'title' => $prefix . 'Pengeluaran Besar di Awal Periode',
                'message' => 'Pengeluaran di awal periode lebih tinggi dari rata-rata.',
                'action' => 'Usahakan meratakan pengeluaran sepanjang periode.',
            ];
        }
        
        return $warnings;
    }

    /**
     * Generate insights
     */
    private function generateInsights(
        array $patterns,
        array $healthScore,
        array $dailyRecommendation,
        float $expenseRatio,
        float $timeRatio,
        string $periodMode,
        bool $isSimulation
    ): array {
        $insights = [];
        $score = $healthScore['score'] ?? 0;
        $prefix = $isSimulation ? '[Simulasi] ' : '';
        
        $periodLabel = match($periodMode) {
            'weekly' => 'minggu',
            'monthly' => 'bulan',
            'simulation' => 'periode simulasi',
            default => 'periode',
        };
        
        // === INSIGHT UTAMA: Rekomendasi harian ===
        $recDaily = $dailyRecommendation['recommended_daily'] ?? 0;
        $daysRemaining = $dailyRecommendation['days_remaining'] ?? 0;
        $remainingBudget = $dailyRecommendation['remaining_budget'] ?? 0;
        
        if ($recDaily > 0 && $daysRemaining > 0) {
            $insightText = "Agar keuangan tetap aman hingga akhir {$periodLabel}, disarankan pengeluaran maksimal Rp " . number_format($recDaily, 0, ',', '.') . " per hari.";
            
            $type = match(true) {
                $score >= 70 => 'positive',
                $score >= 40 => 'warning',
                default => 'danger',
            };
            
            $insights[] = [
                'type' => $type,
                'icon' => $type === 'positive' ? 'check-circle' : 'exclamation-circle',
                'title' => $prefix . 'Rekomendasi Pengeluaran Harian',
                'text' => $insightText,
            ];
        } elseif ($remainingBudget <= 0 && ($patterns['status'] ?? '') !== 'no_income') {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'x-circle',
                'title' => $prefix . 'Anggaran Sudah Habis',
                'text' => 'Pemasukan {$periodLabel} ini sudah habis terpakai. Hindari pengeluaran tambahan.',
            ];
        }
        
        // === INSIGHT: Status keuangan ===
        if (($patterns['status'] ?? '') === 'surplus' && ($patterns['saving_rate'] ?? 0) >= 20) {
            $insights[] = [
                'type' => 'positive',
                'icon' => 'trending-up',
                'title' => $prefix . 'Rasio Tabungan Sehat',
                'text' => 'Kamu menyisihkan ' . $patterns['saving_rate'] . '% dari pemasukan. Pertahankan pola ini.',
            ];
        }
        
        // === INSIGHT: Kecepatan pengeluaran ===
        $spendingSpeed = $patterns['spending_speed'] ?? 0;
        if ($spendingSpeed > 0 && $expenseRatio < 100) {
            if ($spendingSpeed <= 0.8) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => 'check-circle',
                    'title' => $prefix . 'Pengeluaran Terkontrol',
                    'text' => 'Pengeluaranmu ' . $expenseRatio . '% sementara waktu sudah ' . round($timeRatio, 0) . '% berlalu. Sangat baik.',
                ];
            } elseif ($spendingSpeed <= 1.0) {
                $insights[] = [
                    'type' => 'neutral',
                    'icon' => 'info-circle',
                    'title' => $prefix . 'Pengeluaran Sesuai Waktu',
                    'text' => 'Pengeluaranmu seimbang dengan waktu yang berlalu. Tetap jaga konsistensi.',
                ];
            }
        }
        
        // === INSIGHT: Trend vs sebelumnya ===
        if (($patterns['vs_previous'] ?? '') === 'decreasing') {
            $insights[] = [
                'type' => 'positive',
                'icon' => 'arrow-down',
                'title' => $prefix . 'Pengeluaran Menurun',
                'text' => 'Pengeluaran lebih rendah dibanding periode sebelumnya. Usaha penghematanmu terlihat.',
            ];
        }
        
        // === INSIGHT: Pola harian ===
        if (($patterns['spending_consistency'] ?? '') === 'stable') {
            $insights[] = [
                'type' => 'positive',
                'icon' => 'check-circle',
                'title' => $prefix . 'Pola Pengeluaran Stabil',
                'text' => 'Pengeluaran harianmu konsisten. Ini memudahkan perencanaan keuangan.',
            ];
        }
        
        // === INSIGHT: Tidak ada data ===
        if (($patterns['status'] ?? '') === 'no_income' && !isset($patterns['deficit_amount'])) {
            $insights = [[
                'type' => 'neutral',
                'icon' => 'info-circle',
                'title' => 'Belum Ada Transaksi',
                'text' => 'Belum ada transaksi tercatat untuk periode ini. Mulai catat pemasukan dan pengeluaranmu.',
            ]];
        }

        return $insights;
    }
}
