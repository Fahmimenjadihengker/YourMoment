<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'saving_goal_id',
        'amount',
        'note',
        'contributed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contributed_at' => 'date',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function savingGoal(): BelongsTo
    {
        return $this->belongsTo(SavingGoal::class);
    }
}
