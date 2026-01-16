<?php

use App\Http\Controllers\AIRecommendationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletSetupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Wallet Setup Routes (requires auth but NOT wallet.configured)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/wallet/setup', [WalletSetupController::class, 'create'])->name('wallet.setup');
    Route::post('/wallet/setup', [WalletSetupController::class, 'store'])->name('wallet.setup.store');
});

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'wallet.configured'])
    ->name('dashboard');

// Transaction routes (protected by auth middleware)
Route::middleware(['auth', 'verified', 'wallet.configured'])->group(function () {
    // Transaction views
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/income/create', [TransactionController::class, 'createIncome'])->name('transactions.create-income');
    Route::get('/transactions/expense/create', [TransactionController::class, 'createExpense'])->name('transactions.create-expense');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // AI Recommendation route
    Route::get('/ai-recommendation', [AIRecommendationController::class, 'index'])->name('ai-recommendation');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
