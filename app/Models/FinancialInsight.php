<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FinancialInsight Model
 * 
 * Menyimpan insight keuangan per user per bulan.
 * Mencegah duplikasi dengan unique constraint [user_id, period].
 */
class FinancialInsight extends Model
{
    protected $fillable = [
        'user_id',
        'period',
        'summary_text',
        'source',
        'summary_data',
        'health_score',
        'health_label',
    ];

    protected $casts = [
        'summary_data' => 'array',
        'health_score' => 'integer',
    ];

    /**
     * Get the user that owns this insight
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk filter by period
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope untuk filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check apakah insight sudah ada untuk user dan period tertentu
     */
    public static function existsFor(int $userId, string $period): bool
    {
        return static::forUser($userId)->forPeriod($period)->exists();
    }

    /**
     * Get atau create insight untuk user dan period
     */
    public static function getOrCreate(int $userId, string $period, string $summaryText, string $source = 'fallback', ?array $summaryData = null): static
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'period' => $period,
            ],
            [
                'summary_text' => $summaryText,
                'source' => $source,
                'summary_data' => $summaryData,
            ]
        );
    }
}
