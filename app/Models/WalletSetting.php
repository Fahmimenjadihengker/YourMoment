<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletSetting extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'monthly_allowance',
        'weekly_allowance',
        'financial_goal',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'monthly_allowance' => 'decimal:2',
        'weekly_allowance' => 'decimal:2',
        'financial_goal' => 'decimal:2',
    ];

    /**
     * Get the user that owns this wallet setting
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
