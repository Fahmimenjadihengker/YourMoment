<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;

/**
 * FinancialHealthService - Menghitung Skor Kesehatan Keuangan
 * 
 * Menggunakan rule-based logic (TANPA AI) untuk konsistensi dan kecepatan.
 * 
 * BOBOT PENILAIAN (v2 - Improved):
 * - Rasio Tabungan: max 35 poin (saving ratio dari income)
 * - Status Saldo: max 25 poin (surplus/defisit)
 * - Rasio Pengeluaran: max 25 poin (expense vs income ratio)
 * - Diversifikasi Kategori: max 15 poin (tidak terlalu dominan 1 kategori)
 * 
 * TOTAL MAKSIMAL: 100 poin
 * 
 * Formula menggunakan continuous scoring untuk hasil yang lebih halus
 * dan tidak "lompat" antar threshold.
 */
class FinancialHealthService
{
    /**
     * Label berdasarkan range skor
     */
    protected array $labels = [
        [0, 29, 'Buruk', 'red'],
        [30, 49, 'Kurang Baik', 'orange'],
        [50, 69, 'Cukup', 'yellow'],
        [70, 84, 'Baik', 'green'],
        [85, 100, 'Sangat Baik', 'green'],
    ];

    /**
     * Hitung skor kesehatan keuangan dari summary data
     * 
     * Formula v2 - Menggunakan continuous scoring untuk hasil yang lebih halus.
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
        
        // Edge case: no data at all - give neutral score
        $hasNoData = ($totalIncome == 0 && $totalExpense == 0);
        
        if ($hasNoData) {
            return [
                'score' => 50,
                'label' => 'Belum Ada Data',
                'color' => 'gray',
                'breakdown' => [
                    'saving' => ['score' => 0, 'max' => 35, 'description' => 'Rasio Tabungan', 'detail' => 'Belum ada data'],
                    'balance' => ['score' => 12, 'max' => 25, 'description' => 'Status Saldo', 'detail' => 'Belum ada data'],
                    'expense_ratio' => ['score' => 25, 'max' => 25, 'description' => 'Rasio Pengeluaran', 'detail' => 'Belum ada data'],
                    'diversification' => ['score' => 8, 'max' => 15, 'description' => 'Diversifikasi', 'detail' => 'Belum ada data'],
                ],
            ];
        }
        
        // Hitung masing-masing komponen dengan formula continuous
        $savingScore = $this->calculateSavingScore($savingRatio);
        $balanceScore = $this->calculateBalanceScore($totalIncome, $totalExpense);
        $expenseRatioScore = $this->calculateExpenseRatioScore($totalIncome, $totalExpense);
        $diversificationScore = $this->calculateDiversificationScore($topCategoryPercent);

        // Total skor
        $totalScore = $savingScore['score'] + $balanceScore['score'] + $expenseRatioScore['score'] + $diversificationScore['score'];
        $totalScore = (int) min(100, max(0, round($totalScore))); // Clamp 0-100

        // Tentukan label
        $labelData = $this->getLabelForScore($totalScore);

        return [
            'score' => $totalScore,
            'label' => $labelData['label'],
            'color' => $labelData['color'],
            'breakdown' => [
                'saving' => [
                    'score' => $savingScore['score'],
                    'max' => 35,
                    'description' => 'Rasio Tabungan',
                    'detail' => $savingScore['detail'],
                ],
                'balance' => [
                    'score' => $balanceScore['score'],
                    'max' => 25,
                    'description' => 'Status Saldo',
                    'detail' => $balanceScore['detail'],
                ],
                'expense_ratio' => [
                    'score' => $expenseRatioScore['score'],
                    'max' => 25,
                    'description' => 'Rasio Pengeluaran',
                    'detail' => $expenseRatioScore['detail'],
                ],
                'diversification' => [
                    'score' => $diversificationScore['score'],
                    'max' => 15,
                    'description' => 'Diversifikasi',
                    'detail' => $diversificationScore['detail'],
                ],
            ],
        ];
    }

    /**
     * Skor berdasarkan rasio tabungan (max 35 poin)
     * 
     * Menggunakan formula continuous:
     * - Negatif (defisit): 0 poin
     * - 0-10%: 0-15 poin (linear)
     * - 10-20%: 15-25 poin (linear)
     * - 20-30%: 25-32 poin (linear)
     * - >30%: 32-35 poin (diminishing returns)
     */
    private function calculateSavingScore(float $savingRatio): array
    {
        $maxScore = 35;
        
        if ($savingRatio < 0) {
            return ['score' => 0, 'detail' => 'Defisit (pengeluaran > pemasukan)'];
        }

        if ($savingRatio >= 30) {
            // Diminishing returns setelah 30%
            $bonus = min(3, ($savingRatio - 30) / 10);
            return ['score' => (int) round(32 + $bonus), 'detail' => "Rasio tabungan {$savingRatio}% (sangat baik)"];
        }

        if ($savingRatio >= 20) {
            // 20-30% -> 25-32 poin
            $score = 25 + (($savingRatio - 20) / 10) * 7;
            return ['score' => (int) round($score), 'detail' => "Rasio tabungan {$savingRatio}% (baik)"];
        }

        if ($savingRatio >= 10) {
            // 10-20% -> 15-25 poin
            $score = 15 + (($savingRatio - 10) / 10) * 10;
            return ['score' => (int) round($score), 'detail' => "Rasio tabungan {$savingRatio}% (cukup)"];
        }

        // 0-10% -> 0-15 poin
        $score = ($savingRatio / 10) * 15;
        return ['score' => (int) round($score), 'detail' => "Rasio tabungan {$savingRatio}% (perlu ditingkatkan)"];
    }

    /**
     * Skor berdasarkan status saldo (max 25 poin)
     * 
     * Continuous scoring berdasarkan surplus/defisit relatif terhadap income
     */
    private function calculateBalanceScore(float $income, float $expense): array
    {
        $maxScore = 25;
        $income = (float) $income;
        $expense = (float) $expense;
        $difference = $income - $expense;

        // Jika tidak ada income, berikan skor berdasarkan ada tidaknya expense
        if ($income <= 0) {
            if ($expense > 0) {
                return ['score' => 5, 'detail' => 'Ada pengeluaran tanpa pemasukan tercatat'];
            }
            return ['score' => 12, 'detail' => 'Belum ada transaksi'];
        }

        // Hitung surplus/defisit ratio
        $ratio = ($difference / $income) * 100;

        if ($ratio >= 20) {
            return ['score' => 25, 'detail' => 'Surplus signifikan'];
        }
        
        if ($ratio >= 0) {
            // 0-20% surplus -> 15-25 poin
            $score = 15 + ($ratio / 20) * 10;
            return ['score' => (int) round($score), 'detail' => $ratio > 5 ? 'Surplus' : 'Hampir seimbang'];
        }
        
        // Defisit (ratio negatif)
        if ($ratio >= -20) {
            // -20% to 0% -> 5-15 poin
            $score = 15 + ($ratio / 20) * 10; // ratio is negative so this reduces score
            return ['score' => (int) round(max(5, $score)), 'detail' => 'Defisit ringan'];
        }
        
        if ($ratio >= -50) {
            // -50% to -20% -> 2-5 poin
            $score = 5 + (($ratio + 20) / 30) * 3;
            return ['score' => (int) round(max(2, $score)), 'detail' => 'Defisit sedang'];
        }

        return ['score' => 0, 'detail' => 'Defisit berat'];
    }

    /**
     * Skor berdasarkan rasio pengeluaran vs pemasukan (max 25 poin)
     * 
     * Menilai seberapa efisien pengeluaran dibanding pemasukan
     */
    private function calculateExpenseRatioScore(float $income, float $expense): array
    {
        $maxScore = 25;

        if ($income <= 0) {
            if ($expense > 0) {
                return ['score' => 5, 'detail' => 'Hanya ada pengeluaran'];
            }
            return ['score' => 12, 'detail' => 'Belum ada data'];
        }

        $expenseRatio = ($expense / $income) * 100;

        // <= 50% expense = excellent
        if ($expenseRatio <= 50) {
            return ['score' => 25, 'detail' => "Pengeluaran " . round($expenseRatio) . "% dari pemasukan (sangat efisien)"];
        }

        // 50-70% = good
        if ($expenseRatio <= 70) {
            $score = 25 - (($expenseRatio - 50) / 20) * 5;
            return ['score' => (int) round($score), 'detail' => "Pengeluaran " . round($expenseRatio) . "% dari pemasukan (baik)"];
        }

        // 70-90% = okay
        if ($expenseRatio <= 90) {
            $score = 20 - (($expenseRatio - 70) / 20) * 8;
            return ['score' => (int) round($score), 'detail' => "Pengeluaran " . round($expenseRatio) . "% dari pemasukan (cukup)"];
        }

        // 90-100% = tight
        if ($expenseRatio <= 100) {
            $score = 12 - (($expenseRatio - 90) / 10) * 7;
            return ['score' => (int) round(max(5, $score)), 'detail' => "Pengeluaran " . round($expenseRatio) . "% dari pemasukan (ketat)"];
        }

        // > 100% = overspending
        return ['score' => 0, 'detail' => "Pengeluaran " . round($expenseRatio) . "% dari pemasukan (melebihi pemasukan)"];
    }

    /**
     * Skor berdasarkan diversifikasi kategori (max 15 poin)
     * 
     * Continuous scoring berdasarkan dominasi kategori terbesar.
     * Semakin tersebar pengeluaran, semakin baik.
     */
    private function calculateDiversificationScore(float $topCategoryPercent): array
    {
        $maxScore = 15;

        // Tidak ada data kategori
        if ($topCategoryPercent <= 0) {
            return ['score' => 8, 'detail' => 'Belum ada data kategori'];
        }

        // < 30% = sangat terdiversifikasi
        if ($topCategoryPercent < 30) {
            return ['score' => 15, 'detail' => 'Pengeluaran sangat terdiversifikasi'];
        }

        // 30-40% = baik
        if ($topCategoryPercent < 40) {
            $score = 15 - (($topCategoryPercent - 30) / 10) * 3;
            return ['score' => (int) round($score), 'detail' => 'Pengeluaran cukup terdiversifikasi'];
        }

        // 40-60% = cukup
        if ($topCategoryPercent < 60) {
            $score = 12 - (($topCategoryPercent - 40) / 20) * 5;
            return ['score' => (int) round($score), 'detail' => 'Ada kategori dominan (' . round($topCategoryPercent) . '%)'];
        }

        // 60-80% = kurang
        if ($topCategoryPercent < 80) {
            $score = 7 - (($topCategoryPercent - 60) / 20) * 4;
            return ['score' => (int) round(max(3, $score)), 'detail' => 'Kategori terlalu dominan (' . round($topCategoryPercent) . '%)'];
        }

        // >= 80% = sangat dominan
        return ['score' => 2, 'detail' => 'Satu kategori sangat dominan (' . round($topCategoryPercent) . '%)'];
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
     * Dapatkan deskripsi singkat berdasarkan skor
     * Deskripsi profesional tanpa emoji
     */
    public function getDescription(int $score): string
    {
        if ($score >= 85) {
            return 'Kondisi keuanganmu sangat baik. Pertahankan pola pengelolaan yang sudah tepat ini.';
        }

        if ($score >= 70) {
            return 'Keuanganmu dalam kondisi baik. Terus konsisten dengan pola yang ada.';
        }

        if ($score >= 50) {
            return 'Keuanganmu cukup stabil, namun masih ada ruang untuk perbaikan.';
        }

        if ($score >= 30) {
            return 'Perlu perhatian lebih pada pengelolaan pengeluaranmu.';
        }

        return 'Keuanganmu memerlukan perbaikan. Pertimbangkan untuk mengurangi pengeluaran.';
    }

    /**
     * Generate insight teks berdasarkan skor dan breakdown
     * Insight profesional, data-driven, tanpa emoji
     */
    public function generateInsightFromScore(array $scoreData, array $summary): string
    {
        $score = $scoreData['score'];
        $label = $scoreData['label'];
        $breakdown = $scoreData['breakdown'];
        
        $income = $summary['total_income'] ?? 0;
        $expense = $summary['total_expense'] ?? 0;
        $topCategory = $summary['top_category_name'] ?? 'Tidak ada';
        $topPercent = $summary['top_category_percent'] ?? 0;

        // No data case
        if ($income == 0 && $expense == 0) {
            return 'Belum ada transaksi tercatat untuk periode ini. Mulai catat pemasukan dan pengeluaranmu untuk mendapatkan insight yang akurat.';
        }

        $insights = [];

        // Kondisi utama berdasarkan score
        if ($score >= 85) {
            $insights[] = "Kondisi keuanganmu sangat baik dengan skor {$score}/100.";
        } elseif ($score >= 70) {
            $insights[] = "Keuanganmu dalam kondisi baik dengan skor {$score}/100.";
        } elseif ($score >= 50) {
            $insights[] = "Keuanganmu cukup stabil dengan skor {$score}/100, namun masih bisa ditingkatkan.";
        } elseif ($score >= 30) {
            $insights[] = "Skor kesehatan keuanganmu {$score}/100, perlu perhatian lebih.";
        } else {
            $insights[] = "Skor kesehatan keuanganmu {$score}/100, memerlukan perbaikan segera.";
        }

        // Tambah insight spesifik berdasarkan breakdown
        if ($expense > $income) {
            $deficit = $expense - $income;
            $insights[] = "Pengeluaran melebihi pemasukan sebesar Rp" . number_format($deficit, 0, ',', '.') . ".";
        } elseif ($income > 0) {
            $savingRatio = $summary['saving_ratio'] ?? 0;
            if ($savingRatio >= 20) {
                $insights[] = "Rasio tabungan {$savingRatio}% sudah sangat baik.";
            } elseif ($savingRatio >= 10) {
                $insights[] = "Rasio tabungan {$savingRatio}% cukup baik, pertahankan.";
            } elseif ($savingRatio > 0) {
                $insights[] = "Coba tingkatkan rasio tabungan dari {$savingRatio}% menjadi minimal 10%.";
            }
        }

        // Insight kategori dominan
        if ($topPercent >= 50 && $topCategory !== 'Tidak ada') {
            $insights[] = "Kategori {$topCategory} mendominasi {$topPercent}% pengeluaran, pertimbangkan untuk diversifikasi.";
        }

        return implode(' ', $insights);
    }
}
