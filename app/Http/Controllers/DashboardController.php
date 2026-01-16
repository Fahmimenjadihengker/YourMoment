<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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
        $walletSetting = $user->walletSetting;

        // Get current month
        $now = Carbon::now();
        $startOfMonth = $now->startOfMonth();
        $endOfMonth = $now->endOfMonth();

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
