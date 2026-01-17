<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Display income creation form
     */
    public function createIncome()
    {
        $categories = Category::where('type', 'income')->get();
        return view('transactions.create-income', ['categories' => $categories]);
    }

    /**
     * Display expense creation form
     */
    public function createExpense()
    {
        $categories = Category::where('type', 'expense')->get();
        return view('transactions.create-expense', ['categories' => $categories]);
    }

    /**
     * Store transaction in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = auth()->id();

        // === OPTIMIZED: Single wallet update ===
        $wallet = auth()->user()->walletSetting;
        if (!$wallet) {
            $wallet = \App\Models\WalletSetting::create([
                'user_id' => auth()->id(),
                'balance' => 0,
            ]);
        }
        
        // Apply transaction effect
        $amount = (float) $validated['amount'];
        if ($validated['type'] === 'income') {
            $wallet->increment('balance', $amount);
        } else {
            $wallet->decrement('balance', $amount);
        }

        // Create transaction
        $transaction = Transaction::create($validated);

        // === CACHE INVALIDATION: Clear cache untuk bulan ini ===
        $period = CacheService::extractPeriod($validated['transaction_date']);
        $this->cacheService->invalidatePeriod(auth()->id(), $period);

        // Determine redirect
        $type = $validated['type'];
        $message = $type === 'income' ? 'Pemasukan berhasil ditambahkan' : 'Pengeluaran berhasil ditambahkan';

        return redirect()->route('transactions.index')
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil',
                'text' => $message
            ]);
    }

    /**
     * Display all transactions
     */
    public function index()
    {
        $user = auth()->user();
        $filterType = request('type'); // income or expense

        $query = Transaction::forUser($user->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($filterType && in_array($filterType, ['income', 'expense'])) {
            $query->where('type', $filterType);
        }

        $transactions = $query->paginate(15);

        return view('transactions.index', [
            'transactions' => $transactions,
            'filterType' => $filterType,
        ]);
    }

    /**
     * Show edit form for a transaction
     */
    public function edit(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::where('type', $transaction->type)->get();

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $categories,
        ]);
    }

    /**
     * Update transaction in database
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $wallet = auth()->user()->walletSetting;
        
        // Defensive: ensure wallet exists
        if (!$wallet) {
            $wallet = \App\Models\WalletSetting::create([
                'user_id' => auth()->id(),
                'balance' => 0,
            ]);
        }
        
        $oldAmount = (float) $transaction->amount;
        $newAmount = (float) $validated['amount'];

        // === OPTIMIZED: Minimize wallet updates ===
        // Only update if amount changed
        if ($oldAmount !== $newAmount) {
            $difference = $newAmount - $oldAmount;
            
            if ($transaction->type === 'income') {
                $wallet->increment('balance', $difference);
            } else {
                $wallet->decrement('balance', $difference);
            }
        }

        // Update transaction
        $transaction->update($validated);

        // === CACHE INVALIDATION: Clear cache untuk bulan lama & bulan baru ===
        $oldPeriod = CacheService::extractPeriod($transaction->transaction_date);
        $newPeriod = CacheService::extractPeriod($validated['transaction_date']);
        
        $this->cacheService->invalidatePeriod(auth()->id(), $oldPeriod);
        if ($oldPeriod !== $newPeriod) {
            // Jika transaksi dipindah ke bulan lain, invalidate keduanya
            $this->cacheService->invalidatePeriod(auth()->id(), $newPeriod);
        }

        $type = $transaction->type;
        $message = 'Transaksi berhasil diperbarui';

        return redirect()->route('transactions.index')
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil',
                'text' => $message
            ]);
    }

    /**
     * Delete transaction from database
     */
    public function destroy(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $wallet = auth()->user()->walletSetting;

        // Defensive: only update balance if wallet exists
        if ($wallet) {
            $amount = (float) $transaction->amount;
            
            // Reverse transaction effect on balance
            if ($transaction->type === 'income') {
                $wallet->decrement('balance', $amount);
            } else {
                $wallet->increment('balance', $amount);
            }
        }

        // === CACHE INVALIDATION: Clear cache untuk bulan ini ===
        $period = CacheService::extractPeriod($transaction->transaction_date);
        $this->cacheService->invalidatePeriod(auth()->id(), $period);

        $type = $transaction->type;
        $transaction->delete();

        $message = 'Transaksi berhasil dihapus';

        return redirect()->route('transactions.index')
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil dihapus',
                'text' => $message
            ]);
    }
}
