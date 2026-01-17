<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

/**
 * FinancialHealthService - Menghitung Skor Kesehatan Keuangan
 * 
 * Menggunakan rule-based logic (TANPA AI) untuk konsistensi dan kecepatan.
 * 
 * BOBOT PENILAIAN:
 * - Rasio Tabungan: max 40 poin
 * - Status Saldo: max 30 poin
 * - Konsistensi Pengeluaran: max 20 poin
 * - Diversifikasi Kategori: max 10 poin
 * 
 * TOTAL MAKSIMAL: 100 poin
 */
class FinancialHealthService
{
    /**
     * Label berdasarkan range skor
     */
    protected array $labels = [
        [0, 39, 'Tidak Sehat', 'red'],
        [40, 59, 'Kurang Sehat', 'orange'],
        [60, 79, 'Cukup Sehat', 'yellow'],
        [80, 100, 'Sehat', 'green'],
    ];

    /**
     * Hitung skor kesehatan keuangan dari summary data
     * 
     * @param array $summary Data dari InsightService::generateSummary()
     * @param int|null $userId Untuk analisis konsistensi (opsional)
     * @return array ['score' => int, 'label' => string, 'color' => string, 'breakdown' => array]
     */
    public function calculateScore(array $summary, ?int $userId = null): array
    {
        // Defensive: ensure all required keys exist with defaults
        $savingRatio = (float) ($summary['saving_ratio'] ?? 0);
        $totalIncome = (float) ($summary['total_income'] ?? 0);
        $totalExpense = (float) ($summary['total_expense'] ?? 0);
        $topCategoryPercent = (float) ($summary['top_category_percent'] ?? 0);
        $period = $summary['period'] ?? null;
        
        // Edge case: no data at all - give neutral score
        $hasNoData = ($totalIncome == 0 && $totalExpense == 0);
        
        if ($hasNoData) {
            return [
                'score' => 50,
                'label' => 'Belum Ada Data',
                'color' => 'gray',
                'breakdown' => [
                    'saving' => ['score' => 0, 'max' => 40, 'description' => 'Rasio Tabungan'],
                    'balance' => ['score' => 20, 'max' => 30, 'description' => 'Status Saldo'],
                    'consistency' => ['score' => 15, 'max' => 20, 'description' => 'Konsistensi Pengeluaran'],
                    'diversification' => ['score' => 10, 'max' => 10, 'description' => 'Diversifikasi Kategori'],
                ],
            ];
        }
        
        // Hitung masing-masing komponen
        $savingScore = $this->calculateSavingScore($savingRatio);
        $balanceScore = $this->calculateBalanceScore($totalIncome, $totalExpense);
        $consistencyScore = $this->calculateConsistencyScore($userId, $period);
        $diversificationScore = $this->calculateDiversificationScore($topCategoryPercent);

        // Total skor
        $totalScore = $savingScore + $balanceScore + $consistencyScore + $diversificationScore;
        $totalScore = min(100, max(0, $totalScore)); // Clamp 0-100

        // Tentukan label
        $labelData = $this->getLabelForScore($totalScore);

        return [
            'score' => $totalScore,
            'label' => $labelData['label'],
            'color' => $labelData['color'],
            'breakdown' => [
                'saving' => [
                    'score' => $savingScore,
                    'max' => 40,
                    'description' => 'Rasio Tabungan',
                ],
                'balance' => [
                    'score' => $balanceScore,
                    'max' => 30,
                    'description' => 'Status Saldo',
                ],
                'consistency' => [
                    'score' => $consistencyScore,
                    'max' => 20,
                    'description' => 'Konsistensi Pengeluaran',
                ],
                'diversification' => [
                    'score' => $diversificationScore,
                    'max' => 10,
                    'description' => 'Diversifikasi Kategori',
                ],
            ],
        ];
    }

    /**
     * Skor berdasarkan rasio tabungan (max 40 poin)
     * 
     * > 30% → 40 poin
     * 10–30% → 25 poin
     * < 10% → 10 poin
     * Negatif (defisit) → 0 poin
     */
    private function calculateSavingScore(float $savingRatio): int
    {
        if ($savingRatio < 0) {
            return 0;
        }

        if ($savingRatio >= 30) {
            return 40;
        }

        if ($savingRatio >= 10) {
            return 25;
        }

        return 10;
    }

    /**
     * Skor berdasarkan status saldo (max 30 poin)
     * 
     * Surplus → 30 poin
     * Seimbang → 20 poin
     * Defisit → 0 poin
     */
    private function calculateBalanceScore(float $income, float $expense): int
    {
        // Defensive: ensure float
        $income = (float) $income;
        $expense = (float) $expense;
        $difference = $income - $expense;

        if ($difference > 0.01) { // epsilon for float
            return 30; // Surplus
        }

        if (abs($difference) <= 0.01) { // Near zero = seimbang
            return 20; // Seimbang
        }

        return 0; // Defisit
    }

    /**
     * Skor berdasarkan konsistensi pengeluaran (max 20 poin)
     * 
     * Membandingkan pengeluaran bulan ini dengan bulan lalu.
     * Stabil (selisih < 30%) → 20 poin
     * Tidak stabil → 10 poin
     * Tidak ada data → 15 poin (nilai tengah)
     */
    private function calculateConsistencyScore(?int $userId, ?string $currentPeriod): int
    {
        if (!$userId || !$currentPeriod) {
            return 15; // Default jika tidak ada data
        }

        try {
            // Parse current period
            $currentDate = Carbon::createFromFormat('Y-m', $currentPeriod);
            $lastMonth = $currentDate->copy()->subMonth();

            // Ambil pengeluaran bulan ini
            $currentExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [
                    $currentDate->copy()->startOfMonth(),
                    $currentDate->copy()->endOfMonth()
                ])
                ->sum('amount');

            // Ambil pengeluaran bulan lalu
            $lastMonthExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [
                    $lastMonth->copy()->startOfMonth(),
                    $lastMonth->copy()->endOfMonth()
                ])
                ->sum('amount');

            // Jika tidak ada data bulan lalu, anggap stabil
            if ($lastMonthExpense == 0) {
                return 15;
            }

            // Hitung persentase perubahan
            $changePercent = abs(($currentExpense - $lastMonthExpense) / $lastMonthExpense) * 100;

            // Stabil jika perubahan < 30%
            if ($changePercent < 30) {
                return 20;
            }

            return 10;

        } catch (\Exception $e) {
            return 15; // Default jika error
        }
    }

    /**
     * Skor berdasarkan diversifikasi kategori (max 10 poin)
     * 
     * Kategori terbesar < 40% → 10 poin (terdiversifikasi)
     * Kategori terbesar ≥ 40% → 0 poin (terlalu dominan)
     */
    private function calculateDiversificationScore(float $topCategoryPercent): int
    {
        if ($topCategoryPercent < 40) {
            return 10;
        }

        return 0;
    }

    /**
     * Dapatkan label dan warna berdasarkan skor
     */
    private function getLabelForScore(int $score): array
    {
        foreach ($this->labels as [$min, $max, $label, $color]) {
            if ($score >= $min && $score <= $max) {
                return ['label' => $label, 'color' => $color];
            }
        }

        return ['label' => 'Tidak Diketahui', 'color' => 'gray'];
    }

    /**
     * Dapatkan emoji berdasarkan warna/status
     */
    public function getEmoji(string $color): string
    {
        return match ($color) {
            'green' => '💚',
            'yellow' => '💛',
            'orange' => '🧡',
            'red' => '❤️',
            default => '💙',
        };
    }

    /**
     * Dapatkan deskripsi singkat berdasarkan skor
     */
    public function getDescription(int $score): string
    {
        if ($score >= 80) {
            return 'Keuanganmu dalam kondisi sangat baik! Pertahankan pola ini.';
        }

        if ($score >= 60) {
            return 'Keuanganmu cukup baik, tapi masih ada ruang untuk perbaikan.';
        }

        if ($score >= 40) {
            return 'Perlu perhatian lebih pada pengelolaan keuanganmu.';
        }

        return 'Keuanganmu perlu perbaikan segera. Kurangi pengeluaran!';
    }
}
