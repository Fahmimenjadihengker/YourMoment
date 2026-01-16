<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\WalletSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get or create wallet settings (fallback untuk user lama yang belum punya)
        $walletSetting = $user->walletSetting;

        if (!$walletSetting) {
            $walletSetting = WalletSetting::create([
                'user_id' => $user->id,
                'balance' => 0,
                'monthly_allowance' => null,
                'weekly_allowance' => null,
                'financial_goal' => null,
                'notes' => null,
            ]);
        }

        // Get current month
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Get summary data
        $totalIncome = Transaction::forUser($user->id)
            ->income()
            ->dateRange($startOfMonth, $endOfMonth)
            ->sum('amount');

        $totalExpense = Transaction::forUser($user->id)
            ->expense()
            ->dateRange($startOfMonth, $endOfMonth)
            ->sum('amount');

        // Get recent transactions
        $recentTransactions = Transaction::forUser($user->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'walletSetting' => $walletSetting,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'recentTransactions' => $recentTransactions,
            'currentMonth' => $now->format('F Y'),
        ]);
    }
}
