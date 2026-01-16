<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SpendingPatternAnalyzer
{
    protected array $patterns = [];

    /**
     * Keywords untuk deteksi online food delivery
     */
    protected array $onlineFoodKeywords = [
        'shopee', 'shopeefood', 'shopee food',
        'gofood', 'go-food', 'go food',
        'grabfood', 'grab-food', 'grab food',
        'maxim', 'maxim food',
        'traveloka eats',
    ];

    public function __construct(
        protected float $totalExpense7Days,
        protected float $budgetAmount,
        protected string $budgetType,
        protected Collection $categoryBreakdown,
        protected float $walletBalance,
        protected float $financialGoal,
        protected ?Collection $transactions = null
    ) {
    }

    /**
     * Analyze spending patterns
     */
    public function analyze(): array
    {
        $this->detectPatterns();
        return $this->patterns;
    }

    /**
     * Detect all spending patterns
     */
    private function detectPatterns(): void
    {
        // Budget-related patterns
        $this->patterns['isOverspentWeekly'] = $this->budgetType === 'weekly' && $this->totalExpense7Days > $this->budgetAmount;
        $this->patterns['isOverspentMonthly'] = $this->budgetType === 'monthly' && $this->totalExpense7Days > ($this->budgetAmount / 4);

        // Category patterns
        $makan = $this->getCategoryPercentage('Makan');
        $transport = $this->getCategoryPercentage('Transport');
        $nongkrong = $this->getCategoryPercentage('Nongkrong');
        $akademik = $this->getCategoryPercentage('Akademik');

        $this->patterns['highFoodSpending'] = $makan > 50;
        $this->patterns['moderateFoodSpending'] = $makan >= 40 && $makan <= 50;
        $this->patterns['normalFoodSpending'] = $makan > 30 && $makan < 40;
        
        $this->patterns['frequentOnlineFood'] = $this->hasFrequentOnlineFood();
        
        $this->patterns['highHangout'] = $nongkrong > 20;
        $this->patterns['moderateHangout'] = $nongkrong >= 10 && $nongkrong <= 20;
        
        $this->patterns['heavyTransport'] = $transport > 20;
        $this->patterns['moderateTransport'] = $transport >= 10 && $transport <= 20;
        
        $this->patterns['highAcademic'] = $akademik > 10;

        // Savings patterns
        $savingsProgress = $this->financialGoal > 0 ? ($this->walletBalance / $this->financialGoal) * 100 : 0;
        
        $this->patterns['lowSavingsProgress'] = $savingsProgress < 20;
        $this->patterns['fairSavingsProgress'] = $savingsProgress >= 20 && $savingsProgress < 50;
        $this->patterns['goodSavingsProgress'] = $savingsProgress >= 50 && $savingsProgress < 80;
        $this->patterns['nearGoal'] = $savingsProgress >= 80 && $savingsProgress < 100;
        $this->patterns['goalAchieved'] = $savingsProgress >= 100;

        // Combined patterns
        $this->patterns['canImprove'] = !empty(array_filter([
            $this->patterns['isOverspentWeekly'],
            $this->patterns['isOverspentMonthly'],
            $this->patterns['highFoodSpending'],
            $this->patterns['highHangout'],
            $this->patterns['heavyTransport'],
        ]));

        $this->patterns['onTrack'] = !$this->patterns['canImprove'] && $this->patterns['goodSavingsProgress'];
    }

    /**
     * Get category spending percentage
     */
    private function getCategoryPercentage(string $categoryName): float
    {
        if ($this->totalExpense7Days <= 0) {
            return 0;
        }

        $category = $this->categoryBreakdown->firstWhere('name', $categoryName);
        
        if (!$category) {
            return 0;
        }

        return ($category['total'] / $this->totalExpense7Days) * 100;
    }

    /**
     * Check if user has frequent online food orders
     * Detects transactions with notes containing Shopee/GoFood/GrabFood etc.
     */
    private function hasFrequentOnlineFood(): bool
    {
        if (!$this->transactions) {
            return false;
        }

        // Filter transaksi kategori Makan
        $foodTransactions = $this->transactions->filter(function ($transaction) {
            return $transaction->category && 
                   strtolower($transaction->category->name) === 'makan';
        });

        if ($foodTransactions->isEmpty()) {
            return false;
        }

        // Hitung transaksi yang mengandung keyword online food
        $onlineFoodCount = $foodTransactions->filter(function ($transaction) {
            $note = strtolower($transaction->note ?? '');
            $description = strtolower($transaction->description ?? '');
            $searchText = $note . ' ' . $description;
            
            foreach ($this->onlineFoodKeywords as $keyword) {
                if (str_contains($searchText, $keyword)) {
                    return true;
                }
            }
            return false;
        })->count();

        // Jika > 3 transaksi online food dalam seminggu
        return $onlineFoodCount > 3;
    }

    /**
     * Get detected patterns as human-readable array
     */
    public function getDetectedPatterns(): array
    {
        return array_filter($this->patterns, fn($value) => $value === true);
    }

    /**
     * Check if pattern exists and is true
     */
    public function hasPattern(string $pattern): bool
    {
        return $this->patterns[$pattern] ?? false;
    }
}
