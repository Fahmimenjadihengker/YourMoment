<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
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

        // Create transaction
        $transaction = Transaction::create($validated);

        // Update wallet balance
        $wallet = auth()->user()->walletSetting;
        if ($validated['type'] === 'income') {
            $wallet->balance += $validated['amount'];
        } else {
            $wallet->balance -= $validated['amount'];
        }
        $wallet->save();

        // Determine redirect
        $type = $validated['type'];
        $message = ucfirst($type) . ' recorded successfully!';

        return redirect()->route('transactions.index')
            ->with('success', $message);
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
}
