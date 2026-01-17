<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'transaction_time',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the full datetime of transaction (date + time combined)
     * 
     * @return Carbon
     */
    public function getTransactionAtAttribute(): Carbon
    {
        $date = $this->transaction_date instanceof Carbon 
            ? $this->transaction_date->format('Y-m-d') 
            : $this->transaction_date;
        
        $time = $this->transaction_time ?? '00:00:00';
        
        return Carbon::parse("{$date} {$time}");
    }

    /**
     * Get formatted transaction datetime for display
     * 
     * @return string
     */
    public function getFormattedDatetimeAttribute(): string
    {
        return $this->transaction_at->translatedFormat('d M Y, H:i');
    }

    /**
     * Get short formatted date for mobile display
     * 
     * @return string
     */
    public function getShortDatetimeAttribute(): string
    {
        return $this->transaction_at->translatedFormat('d M, H:i');
    }

    /**
     * Get the user that owns this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this transaction
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope untuk filter income transactions
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope untuk filter expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope untuk filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter by category
     */
    public function scopeForCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * Scope untuk optimized summary query (income + expense dalam satu query)
     * Gunakan ini untuk dashboard queries
     */
    public function scopeSummary($query, $userId, $startDate, $endDate)
    {
        return $query->where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense
            ');
    }
}
