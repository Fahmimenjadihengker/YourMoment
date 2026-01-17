<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\WalletSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * InsightService - Agregasi data keuangan user untuk insight
 * 
 * Service ini TIDAK mengandung logika AI.
 * Fokus pada pengumpulan dan perhitungan data keuangan.
 */
class InsightService
{
    /**
     * Generate summary data keuangan untuk periode tertentu
     * 
     * OPTIMIZED:
     * - Single query untuk income/expense dengan aggregation
     * - Single query untuk top category
     * - Minimal database hits
     * 
     * @param int $userId
     * @param string|null $period Format YYYY-MM, default bulan ini
     * @return array
     */
    public function generateSummary(int $userId, ?string $period = null): array
    {
        // Default ke bulan ini jika tidak ada period
        $period = $period ?? Carbon::now()->format('Y-m');
        
        // Parse period
        $date = Carbon::createFromFormat('Y-m', $period);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Ambil wallet setting user (defensive: handle null)
        $walletSetting = WalletSetting::where('user_id', $userId)->first();

        // === OPTIMIZED: Single query untuk income & expense dengan CASE ===
        // Jauh lebih cepat daripada 2 query terpisah
        $totals = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense
            ')
            ->first();

        $totalIncome = (float) ($totals->total_income ?? 0);
        $totalExpense = (float) ($totals->total_expense ?? 0);

        // Hitung status saldo (surplus/defisit)
        $balanceStatus = $this->calculateBalanceStatus($totalIncome, $totalExpense);

        // === OPTIMIZED: Get top category dengan join (1 query) ===
        $topCategory = $this->getTopExpenseCategory($userId, $startOfMonth, $endOfMonth, $totalExpense);

        // Hitung rasio tabungan
        $savingRatio = $this->calculateSavingRatio($totalIncome, $totalExpense);

        return [
            'user_id' => $userId,
            'period' => $period,
            'period_label' => $date->translatedFormat('F Y'),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance_status' => $balanceStatus,
            'top_category_name' => $topCategory['name'],
            'top_category_amount' => $topCategory['amount'],
            'top_category_percent' => $topCategory['percent'],
            'saving_ratio' => $savingRatio,
            'wallet_balance' => $walletSetting?->balance ?? 0,
            'financial_goal' => $walletSetting?->financial_goal ?? 0,
        ];
    }

    /**
     * Hitung status saldo (surplus/defisit/seimbang)
     */
    private function calculateBalanceStatus(float $income, float $expense): string
    {
        // Defensive: ensure float comparison
        $income = (float) $income;
        $expense = (float) $expense;
        $difference = $income - $expense;

        if ($difference > 0.01) { // Use epsilon for float comparison
            return 'Surplus (lebih banyak pemasukan)';
        } elseif ($difference < -0.01) {
            return 'Defisit (lebih banyak pengeluaran)';
        } else {
            return 'Seimbang';
        }
    }

    /**
     * Ambil kategori pengeluaran terbesar
     * 
     * OPTIMIZED:
     * - Single query dengan join ke categories
     * - Jangan iterate data (gunakan aggregation)
     * - Lebih cepat untuk dataset besar
     */
    private function getTopExpenseCategory(int $userId, $startDate, $endDate, float $totalExpense): array
    {
        // === OPTIMIZED: Join dengan categories + aggregate dalam 1 query ===
        $topCategory = Transaction::where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('transactions.category_id', 'categories.name')
            ->orderByDesc('total')
            ->first();

        if (!$topCategory || empty($topCategory->name)) {
            return [
                'name' => 'Tidak ada',
                'amount' => 0,
                'percent' => 0,
            ];
        }

        $percent = $totalExpense > 0 
            ? round(($topCategory->total / $totalExpense) * 100, 1) 
            : 0;

        return [
            'name' => $topCategory->name,
            'amount' => (float) $topCategory->total,
            'percent' => $percent,
        ];
    }

    /**
     * Hitung rasio tabungan (persentase sisa dari pemasukan)
     */
    private function calculateSavingRatio(float $income, float $expense): float
    {
        if ($income <= 0) {
            return 0;
        }

        $saving = $income - $expense;
        $ratio = ($saving / $income) * 100;

        return round($ratio, 1);
    }

    /**
     * Format angka ke format Rupiah
     */
    public function formatRupiah(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}
