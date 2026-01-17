<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * CacheService - Manajemen cache untuk insight & health score
 * 
 * Strategy:
 * - Cache per user + period (YYYY-MM)
 * - Auto invalidate saat transaksi bulan terkait berubah
 * - Fallback ke perhitungan normal jika cache miss
 */
class CacheService
{
    // Cache TTL: 30 hari (karena insight per bulan)
    const CACHE_TTL = 60 * 60 * 24 * 30;
    
    // Cache key patterns
    const INSIGHT_KEY = 'insight:{user_id}:{period}';
    const HEALTH_KEY = 'health:{user_id}:{period}';

    /**
     * Get cached insight atau null jika miss
     */
    public function getInsight(int $userId, string $period): ?array
    {
        return Cache::get($this->buildInsightKey($userId, $period));
    }

    /**
     * Store insight ke cache
     */
    public function putInsight(int $userId, string $period, array $data): void
    {
        Cache::put(
            $this->buildInsightKey($userId, $period),
            $data,
            self::CACHE_TTL
        );
    }

    /**
     * Get cached health score atau null jika miss
     */
    public function getHealth(int $userId, string $period): ?array
    {
        return Cache::get($this->buildHealthKey($userId, $period));
    }

    /**
     * Store health score ke cache
     */
    public function putHealth(int $userId, string $period, array $data): void
    {
        Cache::put(
            $this->buildHealthKey($userId, $period),
            $data,
            self::CACHE_TTL
        );
    }

    /**
     * Invalidate cache untuk period tertentu (saat transaksi berubah)
     * 
     * @param int $userId
     * @param string $period Format YYYY-MM dari transaction_date
     */
    public function invalidatePeriod(int $userId, string $period): void
    {
        Cache::forget($this->buildInsightKey($userId, $period));
        Cache::forget($this->buildHealthKey($userId, $period));
    }

    /**
     * Build insight cache key
     */
    private function buildInsightKey(int $userId, string $period): string
    {
        return "insight:{$userId}:{$period}";
    }

    /**
     * Build health cache key
     */
    private function buildHealthKey(int $userId, string $period): string
    {
        return "health:{$userId}:{$period}";
    }

    /**
     * Extract period (YYYY-MM) dari date
     */
    public static function extractPeriod(Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        return $date->format('Y-m');
    }
}
