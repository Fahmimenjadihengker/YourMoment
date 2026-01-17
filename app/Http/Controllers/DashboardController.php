<?php

namespace App\Http\Controllers;

use App\Models\FinancialInsight;
use App\Models\Transaction;
use App\Models\WalletSetting;
use App\Services\AIService;
use App\Services\CacheService;
use App\Services\FinancialHealthService;
use App\Services\FinancialSummaryService;
use App\Services\InsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected InsightService $insightService;
    protected AIService $aiService;
    protected FinancialHealthService $healthService;
    protected CacheService $cacheService;
    protected FinancialSummaryService $financialService;

    public function __construct(
        InsightService $insightService, 
        AIService $aiService,
        FinancialHealthService $healthService,
        CacheService $cacheService,
        FinancialSummaryService $financialService
    ) {
        $this->insightService = $insightService;
        $this->aiService = $aiService;
        $this->healthService = $healthService;
        $this->cacheService = $cacheService;
        $this->financialService = $financialService;
    }

    /**
     * Display the dashboard
     * 
     * Supports period switching: 'weekly' or 'monthly' (default)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get or create wallet settings (untuk allowance & goals)
        $walletSetting = $user->walletSetting;

        if (!$walletSetting) {
            $walletSetting = WalletSetting::create([
                'user_id' => $user->id,
                'balance' => 0,
                'monthly_allowance' => null,
                'weekly_allowance' => null,
                'financial_goal' => null,
                'notes' => null,
            ]);
        }

        // === TIME PERIOD SELECTION ===
        $periodMode = $request->query('period', 'monthly'); // 'weekly' or 'monthly'
        $now = Carbon::now();
        
        // Determine date range based on period mode
        if ($periodMode === 'weekly') {
            // Minggu ini: Senin - Hari ini
            $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $now->copy()->endOfDay();
            $periodLabel = 'Minggu Ini';
            $periodDetail = $startDate->format('d M') . ' - ' . $endDate->format('d M Y');
        } else {
            // Bulan ini (default)
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $periodLabel = $now->translatedFormat('F Y');
            $periodDetail = 'Bulan ' . $periodLabel;
            $periodMode = 'monthly'; // Ensure valid value
        }

        $currentPeriod = $now->format('Y-m');

        // === SINGLE SOURCE OF TRUTH: Use FinancialSummaryService ===
        $periodSummary = $this->financialService->getSummary($user->id, $startDate, $endDate);
        $totalBalance = $this->financialService->getTotalBalance($user->id);

        $totalIncome = $periodSummary['total_income'];
        $totalExpense = $periodSummary['total_expense'];

        // Update wallet balance to stay in sync (untuk kompatibilitas)
        if (abs($walletSetting->balance - $totalBalance) > 0.01) {
            $walletSetting->balance = $totalBalance;
            $walletSetting->save();
        }

        // === OPTIMIZED: Get recent transactions dengan eager loading ===
        $recentTransactions = Transaction::forUser($user->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('transaction_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // === Generate insight & health score berdasarkan period yang dipilih ===
        $insightData = $this->generateInsightForPeriod($user->id, $startDate, $endDate, $periodMode);

        return view('dashboard', [
            'walletSetting' => $walletSetting,
            'totalBalance' => $totalBalance,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'recentTransactions' => $recentTransactions,
            'currentMonth' => $periodLabel,
            'periodMode' => $periodMode,
            'periodDetail' => $periodDetail,
            'financialInsight' => $insightData['insight'],
            'healthScore' => $insightData['health'],
        ]);
    }

    /**
     * Generate insight dan health score untuk periode tertentu (weekly/monthly)
     * Selalu menggunakan data terbaru dari FinancialSummaryService
     * 
     * @param int $userId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $periodMode 'weekly' or 'monthly'
     * @return array
     */
    private function generateInsightForPeriod(int $userId, Carbon $startDate, Carbon $endDate, string $periodMode): array
    {
        try {
            // Build cache key berdasarkan period range
            $cacheKey = $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
            
            // === STEP 1: Get fresh data dari FinancialSummaryService (always) ===
            $summary = $this->financialService->getSummary($userId, $startDate, $endDate);
            
            // Get top expense categories untuk insight
            $topCategories = $this->financialService->getTopExpenseCategories($userId, $startDate, $endDate, 3);
            
            // Prepare summary dengan format yang dibutuhkan InsightService/HealthService
            $summaryForHealth = [
                'total_income' => $summary['total_income'],
                'total_expense' => $summary['total_expense'],
                'saving_ratio' => $summary['total_income'] > 0 
                    ? round((($summary['total_income'] - $summary['total_expense']) / $summary['total_income']) * 100, 1)
                    : 0,
                'top_category_name' => $topCategories->first()?->name ?? 'Tidak ada',
                'top_category_percent' => $summary['total_expense'] > 0 && $topCategories->first()
                    ? round(($topCategories->first()->total / $summary['total_expense']) * 100, 1)
                    : 0,
                'period' => $startDate->format('Y-m'),
                'period_label' => $periodMode === 'weekly' 
                    ? 'Minggu ini (' . $startDate->format('d M') . ' - ' . $endDate->format('d M') . ')'
                    : $startDate->translatedFormat('F Y'),
            ];

            // === STEP 2: Calculate health score dengan formula baru ===
            $healthResult = $this->healthService->calculateScore($summaryForHealth, $userId);

            // === STEP 3: Generate insight yang selaras dengan health score ===
            // Gunakan AI jika tersedia, atau fallback yang data-driven
            $insightText = $this->healthService->generateInsightFromScore($healthResult, $summaryForHealth);
            
            // Try AI for better insight (optional, with fallback)
            try {
                if (!empty(config('services.openai.api_key'))) {
                    $aiResult = $this->aiService->generateInsight($summaryForHealth);
                    if ($aiResult['success'] && $aiResult['source'] === 'ai') {
                        $insightText = $aiResult['insight'];
                    }
                }
            } catch (\Exception $e) {
                // Keep using health-based insight
            }

            $insightData = [
                'text' => $insightText,
                'source' => 'data',
                'is_cached' => false,
            ];
            
            $healthData = [
                'score' => $healthResult['score'],
                'label' => $healthResult['label'],
                'color' => $healthResult['color'],
                'breakdown' => $healthResult['breakdown'] ?? [],
                'is_cached' => false,
            ];

            return [
                'insight' => $insightData,
                'health' => $healthData,
            ];

        } catch (\Exception $e) {
            // Fallback jika terjadi error
            return [
                'insight' => [
                    'text' => 'Insight keuangan sedang diproses. Data akan tersedia setelah ada transaksi tercatat.',
                    'source' => 'error',
                    'is_cached' => false,
                ],
                'health' => [
                    'score' => 50,
                    'label' => 'Belum Ada Data',
                    'color' => 'gray',
                    'is_cached' => false,
                ],
            ];
        }
    }

    /**
     * Get existing insight dari database atau cache, 
     * atau generate baru jika belum ada
     * 
     * @deprecated Use generateInsightForPeriod instead for real-time data
     */
    private function getOrGenerateInsight(int $userId, string $period): array
    {
        // Redirect ke method baru dengan monthly period
        $now = Carbon::now();
        return $this->generateInsightForPeriod(
            $userId, 
            $now->copy()->startOfMonth(), 
            $now->copy()->endOfMonth(),
            'monthly'
        );
    }

    /**
     * Get color for health label
     */
    private function getColorForLabel(?string $label): string
    {
        return match ($label) {
            'Sangat Baik', 'Baik' => 'green',
            'Cukup' => 'yellow',
            'Kurang Baik' => 'orange',
            'Buruk' => 'red',
            default => 'gray',
        };
    }
}
