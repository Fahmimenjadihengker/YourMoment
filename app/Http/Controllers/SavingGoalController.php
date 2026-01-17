<?php

namespace App\Http\Controllers;

use App\Models\SavingGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavingGoalController extends Controller
{
    /**
     * Display a listing of the saving goals.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all goals grouped by status
        $activeGoals = SavingGoal::forUser($user->id)
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('deadline')
            ->get();

        $completedGoals = SavingGoal::forUser($user->id)
            ->completed()
            ->latest()
            ->take(5)
            ->get();

        // Calculate totals
        $totalSaved = SavingGoal::forUser($user->id)->sum('current_amount');
        $totalTarget = SavingGoal::forUser($user->id)->active()->sum('target_amount');
        $activeCount = $activeGoals->count();
        $completedCount = SavingGoal::forUser($user->id)->completed()->count();

        return view('savings.index', compact(
            'activeGoals',
            'completedGoals',
            'totalSaved',
            'totalTarget',
            'activeCount',
            'completedCount'
        ));
    }

    /**
     * Show the form for creating a new saving goal.
     */
    public function create()
    {
        // Predefined icons and colors
        $icons = ['🎯', '💰', '🏠', '🚗', '📱', '💻', '✈️', '🎓', '💍', '🎁', '🎮', '👗', '⌚', '📷', '🏖️'];
        $colors = ['#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#ef4444', '#06b6d4', '#84cc16'];

        return view('savings.create', compact('icons', 'colors'));
    }

    /**
     * Store a newly created saving goal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'target_amount' => ['required', 'numeric', 'min:1000'],
            'initial_amount' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ], [
            'name.required' => 'Nama target wajib diisi',
            'target_amount.required' => 'Nominal target wajib diisi',
            'target_amount.min' => 'Minimal target Rp1.000',
            'deadline.after' => 'Deadline harus di masa depan',
        ]);

        $goal = SavingGoal::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'current_amount' => $validated['initial_amount'] ?? 0,
            'deadline' => $validated['deadline'] ?? null,
            'icon' => $validated['icon'] ?? '🎯',
            'color' => $validated['color'] ?? '#10b981',
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'active',
        ]);

        // If initial amount provided, create contribution
        if (!empty($validated['initial_amount']) && $validated['initial_amount'] > 0) {
            $goal->contributions()->create([
                'amount' => $validated['initial_amount'],
                'note' => 'Dana awal',
                'contributed_at' => now()->toDateString(),
            ]);
        }

        return redirect()->route('savings.show', $goal)
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil',
                'text' => 'Target tabungan berhasil dibuat'
            ]);
    }

    /**
     * Display the specified saving goal.
     */
    public function show(SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        // Load contributions
        $contributions = $goal->contributions()
            ->latest('contributed_at')
            ->take(10)
            ->get();

        // Get AI recommendation
        $recommendation = $goal->getRecommendation();

        return view('savings.show', compact('goal', 'contributions', 'recommendation'));
    }

    /**
     * Show the form for editing the specified saving goal.
     */
    public function edit(SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        // Cannot edit completed/cancelled goals
        if ($goal->status !== 'active') {
            return redirect()->route('savings.show', $goal)
                ->with('swal', [
                    'type' => 'warning',
                    'title' => 'Tidak bisa diedit',
                    'text' => 'Target yang sudah selesai atau dibatalkan tidak bisa diedit.'
                ]);
        }

        // Predefined icons and colors
        $icons = ['🎯', '💰', '🏠', '🚗', '📱', '💻', '✈️', '🎓', '💍', '🎁', '🎮', '👗', '⌚', '📷', '🏖️'];
        $colors = ['#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#ef4444', '#06b6d4', '#84cc16'];

        return view('savings.edit', compact('goal', 'icons', 'colors'));
    }

    /**
     * Update the specified saving goal.
     */
    public function update(Request $request, SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        // Cannot update completed/cancelled goals
        if ($goal->status !== 'active') {
            return redirect()->route('savings.show', $goal)
                ->with('swal', [
                    'type' => 'warning',
                    'title' => 'Tidak bisa diperbarui',
                    'text' => 'Target yang sudah selesai atau dibatalkan tidak bisa diperbarui.'
                ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'target_amount' => ['required', 'numeric', 'min:1000'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ], [
            'name.required' => 'Nama target wajib diisi',
            'target_amount.required' => 'Nominal target wajib diisi',
            'target_amount.min' => 'Minimal target Rp1.000',
            'deadline.after' => 'Deadline harus di masa depan',
        ]);

        // Validate target amount is not less than current amount
        if ($validated['target_amount'] < $goal->current_amount) {
            return back()->withInput()->withErrors([
                'target_amount' => 'Target tidak boleh kurang dari jumlah yang sudah terkumpul (Rp ' . number_format($goal->current_amount, 0, ',', '.') . ')'
            ]);
        }

        $goal->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'deadline' => $validated['deadline'] ?? null,
            'icon' => $validated['icon'] ?? $goal->icon,
            'color' => $validated['color'] ?? $goal->color,
            'priority' => $validated['priority'] ?? $goal->priority,
        ]);

        // Check if target is now completed after updating target amount
        if ($goal->current_amount >= $validated['target_amount']) {
            $goal->update(['status' => 'completed']);
        }

        return redirect()->route('savings.show', $goal)
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil',
                'text' => 'Target tabungan berhasil diperbarui'
            ]);
    }

    /**
     * Add funds to a saving goal.
     */
    public function addFunds(Request $request, SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        // Cannot add funds to completed/cancelled goals
        if ($goal->status !== 'active') {
            return back()->with('swal', [
                'type' => 'error',
                'title' => 'Tidak dapat menambah dana',
                'text' => 'Target yang sudah selesai atau dibatalkan tidak bisa ditambah dana.'
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Nominal wajib diisi',
            'amount.min' => 'Minimal Rp1.000',
        ]);

        // Add contribution
        $goal->addContribution(
            $validated['amount'],
            $validated['note'] ?? null
        );

        $isCompleted = $goal->fresh()->status === 'completed';

        return back()->with('swal', [
            'type' => 'success',
            'title' => $isCompleted ? 'Selamat!' : 'Berhasil',
            'text' => $isCompleted ? 'Target tabungan tercapai!' : 'Dana berhasil ditambahkan'
        ]);
    }

    /**
     * Remove the specified saving goal.
     */
    public function destroy(SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goalName = $goal->name;
        $goal->delete();

        return redirect()->route('savings.index')
            ->with('swal', [
                'type' => 'success',
                'title' => 'Berhasil dihapus',
                'text' => "Target \"{$goalName}\" berhasil dihapus"
            ]);
    }

    /**
     * Cancel a saving goal.
     */
    public function cancel(SavingGoal $goal)
    {
        // Ensure user owns this goal
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->cancel();

        return back()->with('swal', [
            'type' => 'success',
            'title' => 'Berhasil',
            'text' => 'Target tabungan dibatalkan'
        ]);
    }
}
