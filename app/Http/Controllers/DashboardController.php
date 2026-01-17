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
     */
    public function index()
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

        // Get current month
        $now = Carbon::now();
        $currentPeriod = $now->format('Y-m');

        // === SINGLE SOURCE OF TRUTH: Use FinancialSummaryService ===
        $monthlySummary = $this->financialService->getSummary($user->id);
        $totalBalance = $this->financialService->getTotalBalance($user->id);

        $totalIncome = $monthlySummary['total_income'];
        $totalExpense = $monthlySummary['total_expense'];

        // Update wallet balance to stay in sync (untuk kompatibilitas)
        if (abs($walletSetting->balance - $totalBalance) > 0.01) {
            $walletSetting->balance = $totalBalance;
            $walletSetting->save();
        }

        // === OPTIMIZED: Get recent transactions dengan eager loading ===
        $recentTransactions = Transaction::forUser($user->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // === OPTIMIZED: Get insight & health dengan cache + fallback ===
        $insightData = $this->getOrGenerateInsight($user->id, $currentPeriod);

        return view('dashboard', [
            'walletSetting' => $walletSetting,
            'totalBalance' => $totalBalance,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'recentTransactions' => $recentTransactions,
            'currentMonth' => $now->format('F Y'),
            'financialInsight' => $insightData['insight'],
            'healthScore' => $insightData['health'],
        ]);
    }

    /**
     * Get existing insight dari database atau cache, 
     * atau generate baru jika belum ada
     */
    private function getOrGenerateInsight(int $userId, string $period): array
    {
        // === STEP 1: Check cache dulu (fastest) ===
        $cachedInsight = $this->cacheService->getInsight($userId, $period);
        $cachedHealth = $this->cacheService->getHealth($userId, $period);
        
        if ($cachedInsight && $cachedHealth) {
            return [
                'insight' => $cachedInsight,
                'health' => $cachedHealth,
            ];
        }

        // === STEP 2: Check database (fallback to database if cache miss) ===
        $existingInsight = FinancialInsight::forUser($userId)
            ->forPeriod($period)
            ->first();

        if ($existingInsight) {
            // Return dari database dan update cache
            $insightData = [
                'text' => $existingInsight->summary_text,
                'source' => $existingInsight->source,
                'is_cached' => false,
            ];
            
            $healthData = [
                'score' => $existingInsight->health_score ?? 0,
                'label' => $existingInsight->health_label ?? 'Tidak Diketahui',
                'color' => $this->getColorForLabel($existingInsight->health_label),
                'is_cached' => false,
            ];

            // Cache untuk akses berikutnya
            $this->cacheService->putInsight($userId, $period, $insightData);
            $this->cacheService->putHealth($userId, $period, $healthData);

            return [
                'insight' => $insightData,
                'health' => $healthData,
            ];
        }

        // === STEP 3: Generate insight baru (calculate once) ===
        try {
            // Step 3a: Agregasi data keuangan SEKALI
            $summary = $this->insightService->generateSummary($userId, $period);

            // Step 3b: Generate insight via AI (dengan fallback otomatis)
            $aiResult = $this->aiService->generateInsight($summary);

            // Step 3c: Calculate health score DENGAN summary yang sama
            $healthResult = $this->healthService->calculateScore($summary, $userId);

            // Step 3d: Simpan ke database
            FinancialInsight::create([
                'user_id' => $userId,
                'period' => $period,
                'summary_text' => $aiResult['insight'],
                'source' => $aiResult['source'],
                'summary_data' => $summary,
                'health_score' => $healthResult['score'],
                'health_label' => $healthResult['label'],
            ]);

            // Step 3e: Cache untuk akses berikutnya
            $insightData = [
                'text' => $aiResult['insight'],
                'source' => $aiResult['source'],
                'is_cached' => false,
            ];
            
            $healthData = [
                'score' => $healthResult['score'],
                'label' => $healthResult['label'],
                'color' => $healthResult['color'],
                'breakdown' => $healthResult['breakdown'],
                'is_cached' => false,
            ];

            $this->cacheService->putInsight($userId, $period, $insightData);
            $this->cacheService->putHealth($userId, $period, $healthData);

            return [
                'insight' => $insightData,
                'health' => $healthData,
            ];

        } catch (\Exception $e) {
            // Fallback jika terjadi error
            $insightData = [
                'text' => 'Insight keuangan sedang diproses. Silakan refresh halaman dalam beberapa saat.',
                'source' => 'error',
                'is_cached' => false,
            ];
            
            $healthData = [
                'score' => 0,
                'label' => 'Tidak Diketahui',
                'color' => 'gray',
                'is_cached' => false,
            ];

            return [
                'insight' => $insightData,
                'health' => $healthData,
            ];
        }
    }

    /**
     * Get color for health label
     */
    private function getColorForLabel(?string $label): string
    {
        return match ($label) {
            'Sehat' => 'green',
            'Cukup Sehat' => 'yellow',
            'Kurang Sehat' => 'orange',
            'Tidak Sehat' => 'red',
            default => 'gray',
        };
    }
}
