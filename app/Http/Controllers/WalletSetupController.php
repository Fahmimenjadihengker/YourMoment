<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\WalletSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletSetupController extends Controller
{
    /**
     * Show the wallet setup form
     */
    public function create()
    {
        $user = auth()->user();

        // Jika user sudah punya wallet dengan data lengkap, redirect ke dashboard
        if ($user->walletSetting && $this->isWalletConfigured($user->walletSetting)) {
            return redirect()->route('dashboard');
        }

        return view('wallet.setup');
    }

    /**
     * Store wallet settings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_amount' => 'required|numeric|min:0',
            'budget_type' => 'required|in:weekly,monthly',
            'financial_goal' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        // Cek apakah wallet sudah dikonfigurasi (untuk mencegah double submit)
        if ($user->walletSetting && $this->isWalletConfigured($user->walletSetting)) {
            return redirect()->route('dashboard');
        }

        // Gunakan transaction untuk memastikan data konsisten
        DB::transaction(function () use ($validated, $user) {
            // Tentukan kolom berdasarkan budget_type
            $walletData = [
                'user_id' => $user->id,
                'balance' => $validated['budget_amount'],
                'monthly_allowance' => $validated['budget_type'] === 'monthly' ? $validated['budget_amount'] : null,
                'weekly_allowance' => $validated['budget_type'] === 'weekly' ? $validated['budget_amount'] : null,
                'financial_goal' => $validated['financial_goal'],
                'notes' => 'Type: ' . $validated['budget_type'],
            ];

            // Update atau create wallet settings
            WalletSetting::updateOrCreate(
                ['user_id' => $user->id],
                $walletData
            );

            // Buat transaksi income awal jika budget_amount > 0
            if ($validated['budget_amount'] > 0) {
                $this->createInitialIncomeTransaction($user->id, $validated['budget_amount']);
            }
        });

        return redirect()->route('dashboard')
            ->with('swal', [
                'type' => 'success',
                'title' => 'Selamat datang!',
                'text' => 'Pengaturan wallet berhasil disimpan. Selamat menggunakan YourMoment!'
            ]);
    }

    /**
     * Create initial income transaction for new user
     */
    private function createInitialIncomeTransaction(int $userId, float $amount): void
    {
        // Cek apakah sudah ada transaksi dengan note "Uang jajan awal" untuk user ini
        $existingInitialTransaction = Transaction::where('user_id', $userId)
            ->where('description', 'Uang jajan awal')
            ->exists();

        if ($existingInitialTransaction) {
            return; // Jangan buat duplikat
        }

        // Ambil kategori income default (prioritas: Gaji, fallback: kategori income pertama)
        $incomeCategory = Category::where('type', 'income')
            ->where('name', 'Gaji')
            ->first();

        if (!$incomeCategory) {
            $incomeCategory = Category::where('type', 'income')->first();
        }

        // Jika tidak ada kategori income sama sekali, skip
        if (!$incomeCategory) {
            return;
        }

        // Buat transaksi income awal
        Transaction::create([
            'user_id' => $userId,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => $amount,
            'description' => 'Uang jajan awal',
            'transaction_date' => Carbon::today(),
            'payment_method' => null,
        ]);
    }

    /**
     * Check if wallet is properly configured
     */
    private function isWalletConfigured(WalletSetting $wallet): bool
    {
        return $wallet->monthly_allowance > 0 || $wallet->weekly_allowance > 0;
    }
}
