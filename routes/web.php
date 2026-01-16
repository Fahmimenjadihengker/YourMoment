<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Transaction routes (protected by auth middleware)
Route::middleware(['auth', 'verified'])->group(function () {
    // Transaction views
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/income/create', [TransactionController::class, 'createIncome'])->name('transactions.create-income');
    Route::get('/transactions/expense/create', [TransactionController::class, 'createExpense'])->name('transactions.create-expense');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
