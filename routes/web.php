<?php

use App\Http\Controllers\AIRecommendationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingGoalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletSetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - YourMoment Financial App (PWA Mobile-First)
|--------------------------------------------------------------------------
|
| Route Structure:
| 1. Public routes (redirect to dashboard)
| 2. Wallet setup (first-time user)
| 3. Dashboard (Tab 1)
| 4. Transactions (Tab 2)
| 5. Saving Goals (Tab 3)
| 6. AI Features (Tab 4)
| 7. Profile (Tab 5)
|
*/

// ============================================================
// PUBLIC ROUTES
// ============================================================

// Redirect root to dashboard (or login if not authenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // --------------------------------------------------------
    // WALLET SETUP (First-time user)
    // --------------------------------------------------------
    Route::get('/wallet/setup', [WalletSetupController::class, 'create'])
        ->name('wallet.setup');
    Route::post('/wallet/setup', [WalletSetupController::class, 'store'])
        ->name('wallet.store');

    // --------------------------------------------------------
    // DASHBOARD & INSIGHTS (Tab 1: Home)
    // --------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // --------------------------------------------------------
    // TRANSACTIONS (Tab 2: Transaksi)
    // --------------------------------------------------------
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // List all transactions
        Route::get('/', [TransactionController::class, 'index'])
            ->name('index');
        
        // Create forms
        Route::get('/income/create', [TransactionController::class, 'createIncome'])
            ->name('create-income');
        Route::get('/expense/create', [TransactionController::class, 'createExpense'])
            ->name('create-expense');
        
        // Store new transaction
        Route::post('/', [TransactionController::class, 'store'])
            ->name('store');
        
        // Edit transaction
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])
            ->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])
            ->name('update');
        
        // Delete transaction
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])
            ->name('destroy');
    });

    // --------------------------------------------------------
    // SAVING GOALS (Tab 3: Target)
    // --------------------------------------------------------
    Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [SavingGoalController::class, 'index'])
            ->name('index');
        Route::get('/create', [SavingGoalController::class, 'create'])
            ->name('create');
        Route::post('/', [SavingGoalController::class, 'store'])
            ->name('store');
        Route::get('/{goal}', [SavingGoalController::class, 'show'])
            ->name('show');
        Route::get('/{goal}/edit', [SavingGoalController::class, 'edit'])
            ->name('edit');
        Route::put('/{goal}', [SavingGoalController::class, 'update'])
            ->name('update');
        Route::post('/{goal}/add-funds', [SavingGoalController::class, 'addFunds'])
            ->name('add-funds');
        Route::post('/{goal}/cancel', [SavingGoalController::class, 'cancel'])
            ->name('cancel');
        Route::delete('/{goal}', [SavingGoalController::class, 'destroy'])
            ->name('destroy');
    });

    // --------------------------------------------------------
    // AI FEATURES (Tab 4: AI)
    // --------------------------------------------------------
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/chat', [AIRecommendationController::class, 'chat'])
            ->name('chat');
        Route::post('/chat', [AIRecommendationController::class, 'sendMessage'])
            ->name('send');
    });
    
    // Legacy AI recommendation route
    Route::get('/ai-recommendation', [AIRecommendationController::class, 'index'])
        ->name('ai-recommendation');

    // --------------------------------------------------------
    // PROFILE MANAGEMENT (Tab 5: Profil)
    // --------------------------------------------------------
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('destroy');
    });
});

// ============================================================
// AUTH ROUTES (login, register, password reset, etc.)
// ============================================================
require __DIR__.'/auth.php';
