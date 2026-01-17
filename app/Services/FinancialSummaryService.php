<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\WalletSetting;
use App\Models\SavingGoal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FinancialSummaryService - Single Source of Truth untuk data keuangan
 * 
 * SEMUA controller/service yang membutuhkan data keuangan HARUS melalui service ini.
 * Ini memastikan konsistensi perhitungan di seluruh aplikasi.
 */
class FinancialSummaryService
{
    /**
     * Get financial summary untuk user pada periode tertentu
     * 
     * @param int $userId
     * @param Carbon|null $startDate Start of period (default: start of current month)
     * @param Carbon|null $endDate End of period (default: end of current month)
     * @return array
     */
    public function getSummary(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        // Default ke bulan ini
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();
        
        // Single query untuk income & expense
        $totals = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) as total_expense,
                COUNT(*) as transaction_count
            ')
            ->first();

        $totalIncome = (float) ($totals->total_income ?? 0);
        $totalExpense = (float) ($totals->total_expense ?? 0);
        $balance = $totalIncome - $totalExpense;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'transaction_count' => (int) ($totals->transaction_count ?? 0),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'label' => $startDate->translatedFormat('F Y'),
            ],
        ];
    }

    /**
     * Get ALL TIME balance (total income - total expense, tanpa filter periode)
     * 
     * @param int $userId
     * @return float
     */
    public function getTotalBalance(int $userId): float
    {
        $totals = Transaction::where('user_id', $userId)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END), 0) as total_expense
            ')
            ->first();

        return (float) ($totals->total_income ?? 0) - (float) ($totals->total_expense ?? 0);
    }

    /**
     * Get summary untuk 7 hari terakhir
     * 
     * @param int $userId
     * @return array
     */
    public function getWeeklySummary(int $userId): array
    {
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();
        
        return $this->getSummary($userId, $startDate, $endDate);
    }

    /**
     * Get top expense categories untuk periode tertentu
     * 
     * @param int $userId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopExpenseCategories(
        int $userId, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null, 
        int $limit = 5
    ) {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        return Transaction::where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name,
                categories.icon,
                categories.color,
                SUM(transactions.amount) as total,
                COUNT(*) as count
            ')
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get saving goals summary untuk user
     * 
     * @param int $userId
     * @return array
     */
    public function getSavingGoalsSummary(int $userId): array
    {
        $goals = SavingGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        return [
            'total_goals' => $goals->count(),
            'total_target' => $goals->sum('target_amount'),
            'total_saved' => $goals->sum('current_amount'),
            'goals' => $goals->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target' => $goal->target_amount,
                    'current' => $goal->current_amount,
                    'progress' => $goal->progress,
                    'deadline' => $goal->deadline?->format('Y-m-d'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Get complete financial context untuk AI
     * 
     * @param int $userId
     * @return array
     */
    public function getAIContext(int $userId): array
    {
        $walletSetting = WalletSetting::where('user_id', $userId)->first();
        
        $monthlySummary = $this->getSummary($userId);
        $weeklySummary = $this->getWeeklySummary($userId);
        $totalBalance = $this->getTotalBalance($userId);
        $topCategories = $this->getTopExpenseCategories($userId);
        $savingGoals = $this->getSavingGoalsSummary($userId);

        return [
            // Real calculated balance (tidak dari DB)
            'balance' => $totalBalance,
            
            // Wallet settings (allowance, goals)
            'monthly_allowance' => $walletSetting?->monthly_allowance,
            'weekly_allowance' => $walletSetting?->weekly_allowance,
            'financial_goal' => $walletSetting?->financial_goal,
            
            // Monthly data
            'monthly_income' => $monthlySummary['total_income'],
            'monthly_expense' => $monthlySummary['total_expense'],
            'monthly_balance' => $monthlySummary['balance'],
            
            // Weekly data
            'weekly_expense' => $weeklySummary['total_expense'],
            
            // Categories
            'category_breakdown' => $topCategories->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'total' => $cat->total,
                    'count' => $cat->count,
                ];
            })->values()->toArray(),
            
            // Saving goals
            'saving_goals' => $savingGoals['goals'],
            
            // Meta
            'current_date' => Carbon::now()->format('Y-m-d'),
            'current_month' => Carbon::now()->translatedFormat('F Y'),
        ];
    }

    /**
     * Sync wallet balance dengan actual transactions
     * Gunakan ini untuk memperbaiki data yang tidak sinkron
     * 
     * @param int $userId
     * @return float New balance
     */
    public function syncWalletBalance(int $userId): float
    {
        $calculatedBalance = $this->getTotalBalance($userId);
        
        WalletSetting::updateOrCreate(
            ['user_id' => $userId],
            ['balance' => $calculatedBalance]
        );
        
        return $calculatedBalance;
    }

    /**
     * Sync ALL users' wallet balances
     * 
     * @return int Number of users synced
     */
    public function syncAllWalletBalances(): int
    {
        $count = 0;
        
        $userIds = Transaction::distinct()->pluck('user_id');
        
        foreach ($userIds as $userId) {
            $this->syncWalletBalance($userId);
            $count++;
        }
        
        return $count;
    }
}
