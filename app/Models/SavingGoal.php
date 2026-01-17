<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SavingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'deadline',
        'icon',
        'color',
        'status',
        'priority',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SavingContribution::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    /**
     * Get progress percentage (0-100)
     */
    public function getProgressAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        return min(100, round(($this->current_amount / $this->target_amount) * 100, 1));
    }

    /**
     * Get remaining amount to reach target
     */
    public function getRemainingAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Get days remaining until deadline
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline) {
            return null;
        }
        return max(0, Carbon::now()->diffInDays($this->deadline, false));
    }

    /**
     * Check if deadline has passed
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->deadline) {
            return false;
        }
        return Carbon::now()->isAfter($this->deadline);
    }

    /**
     * Get daily saving needed to reach target on time
     */
    public function getDailySavingNeededAttribute(): ?float
    {
        if (!$this->deadline || $this->days_remaining <= 0) {
            return null;
        }
        return round($this->remaining / $this->days_remaining, 0);
    }

    /**
     * Get weekly saving needed to reach target on time
     */
    public function getWeeklySavingNeededAttribute(): ?float
    {
        if (!$this->deadline) {
            return null;
        }
        $weeksRemaining = ceil($this->days_remaining / 7);
        if ($weeksRemaining <= 0) {
            return null;
        }
        return round($this->remaining / $weeksRemaining, 0);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => $this->is_overdue ? 'orange' : 'blue',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'high' => 'Prioritas Tinggi',
            'medium' => 'Prioritas Sedang',
            'low' => 'Prioritas Rendah',
            default => 'Normal',
        };
    }

    // ============================================================
    // METHODS
    // ============================================================

    /**
     * Add contribution to this goal
     */
    public function addContribution(float $amount, ?string $note = null, ?string $date = null): SavingContribution
    {
        $contribution = $this->contributions()->create([
            'amount' => $amount,
            'note' => $note,
            'contributed_at' => $date ?? now()->toDateString(),
        ]);

        // Update current amount
        $this->increment('current_amount', $amount);

        // Check if goal is completed
        if ($this->fresh()->current_amount >= $this->target_amount) {
            $this->update(['status' => 'completed']);
        }

        return $contribution;
    }

    /**
     * Mark goal as cancelled
     */
    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Get AI recommendation for this goal
     */
    public function getRecommendation(): string
    {
        $progress = $this->progress;
        $daysRemaining = $this->days_remaining;
        $dailyNeeded = $this->daily_saving_needed;

        if ($this->status === 'completed') {
            return "🎉 Selamat! Kamu sudah mencapai target {$this->name}!";
        }

        if ($this->is_overdue) {
            return "⏰ Deadline sudah lewat. Pertimbangkan untuk memperpanjang deadline atau menyesuaikan target.";
        }

        if ($progress >= 80) {
            return "🔥 Hampir sampai! Tinggal " . number_format($this->remaining, 0, ',', '.') . " lagi untuk mencapai {$this->name}.";
        }

        if ($progress >= 50) {
            return "💪 Sudah setengah jalan! Konsisten nabung Rp" . number_format($dailyNeeded ?? 0, 0, ',', '.') . "/hari ya.";
        }

        if ($daysRemaining && $daysRemaining < 30) {
            return "⚡ Waktunya tinggal {$daysRemaining} hari. Perlu nabung Rp" . number_format($dailyNeeded ?? 0, 0, ',', '.') . "/hari.";
        }

        return "🎯 Tetap semangat! Target {$this->name} sudah {$progress}% tercapai.";
    }
}
