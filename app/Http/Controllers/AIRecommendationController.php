<?php

namespace App\Http\Controllers;

use App\Services\AIRecommendationService;
use App\Services\FinancialAnalysisService;
use Illuminate\Http\Request;

class AIRecommendationController extends Controller
{
    protected AIRecommendationService $aiService;
    protected FinancialAnalysisService $analysisService;

    public function __construct(
        AIRecommendationService $aiService,
        FinancialAnalysisService $analysisService
    ) {
        $this->aiService = $aiService;
        $this->analysisService = $analysisService;
    }

    /**
     * Display Financial Analysis page
     * 
     * Supports 3 modes:
     * - 'monthly' (default): Analisis bulanan dari database
     * - 'weekly': Analisis mingguan dari database
     * - 'simulation': Analisis dari input manual (tidak menyentuh database)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get period mode from query (default: monthly)
        $periodMode = $request->query('period', 'monthly');
        if (!in_array($periodMode, ['weekly', 'monthly', 'simulation'])) {
            $periodMode = 'monthly';
        }

        try {
            // Mode SIMULASI: tampilkan form, tidak ada analisis awal
            if ($periodMode === 'simulation') {
                return view('ai-recommendation', $this->getSimulationViewData());
            }
            
            // Mode WEEKLY / MONTHLY: ambil data dari database
            $analysis = $this->analysisService->generateAnalysis($user->id, $periodMode);

            return view('ai-recommendation', [
                // Period info
                'periodMode' => $periodMode,
                'period' => $analysis['period'],
                'comparisonPeriod' => $analysis['comparison_period'],
                'isSimulation' => false,
                
                // Financial data (BERBASIS PERIODE)
                'current' => $analysis['current'],
                'previous' => $analysis['previous'],
                'expenseRatio' => $analysis['expense_ratio'],
                'timeRatio' => $analysis['time_ratio'],
                
                // Saldo total (VISUAL ONLY)
                'totalBalance' => $analysis['total_balance'],
                'totalBalanceNote' => $analysis['total_balance_note'] ?? '',
                
                // Legacy vars
                'totalExpense' => $analysis['current']['total_expense'],
                'totalIncome' => $analysis['current']['total_income'],
                
                // Daily patterns & recommendations
                'dailyPatterns' => $analysis['daily_patterns'],
                'dailyRecommendation' => $analysis['daily_recommendation'],
                
                // Analysis results
                'trend' => $analysis['trend'],
                'composition' => $analysis['composition'],
                'patterns' => $analysis['patterns'],
                'insights' => $analysis['insights'],
                'warnings' => $analysis['warnings'],
                
                // Health score
                'healthScore' => $analysis['health_score'],
                
                // Categories
                'topCategories' => $analysis['top_categories'],
                
                // Full analysis
                'walletSetting' => $user->walletSetting,
                'analysis' => $analysis,
                
                // Simulation input (kosong untuk mode non-simulasi)
                'simulationInput' => null,
            ]);
        } catch (\Exception $e) {
            return view('ai-recommendation', $this->getErrorViewData($periodMode, $e->getMessage()));
        }
    }

    /**
     * Process simulation form submission
     * Data di-input manual, TIDAK menyentuh database
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'income' => ['required', 'numeric', 'min:0'],
            'expense' => ['required', 'numeric', 'min:0'],
            'days_remaining' => ['required', 'integer', 'min:1', 'max:365'],
            'total_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        try {
            $input = [
                'income' => $request->input('income'),
                'expense' => $request->input('expense'),
                'days_remaining' => $request->input('days_remaining'),
                'total_days' => $request->input('total_days', 30),
            ];
            
            // Generate analisis simulasi (TIDAK menyentuh database)
            $analysis = $this->analysisService->generateSimulationAnalysis($input);

            return view('ai-recommendation', [
                // Period info
                'periodMode' => 'simulation',
                'period' => $analysis['period'],
                'comparisonPeriod' => null,
                'isSimulation' => true,
                
                // Financial data
                'current' => $analysis['current'],
                'previous' => null,
                'expenseRatio' => $analysis['expense_ratio'],
                'timeRatio' => $analysis['time_ratio'],
                
                // Saldo total tidak ada di simulasi
                'totalBalance' => null,
                'totalBalanceNote' => 'Mode simulasi tidak menampilkan saldo total.',
                
                // Legacy vars
                'totalExpense' => $analysis['current']['total_expense'],
                'totalIncome' => $analysis['current']['total_income'],
                
                // Daily patterns & recommendations
                'dailyPatterns' => $analysis['daily_patterns'],
                'dailyRecommendation' => $analysis['daily_recommendation'],
                
                // Analysis results
                'trend' => $analysis['trend'],
                'composition' => $analysis['composition'],
                'patterns' => $analysis['patterns'],
                'insights' => $analysis['insights'],
                'warnings' => $analysis['warnings'],
                
                // Health score
                'healthScore' => $analysis['health_score'],
                
                // Categories (kosong di simulasi)
                'topCategories' => [],
                
                // Full analysis
                'walletSetting' => null,
                'analysis' => $analysis,
                
                // Simulation input (untuk re-populate form)
                'simulationInput' => $input,
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses simulasi: ' . $e->getMessage());
        }
    }

    /**
     * Get view data untuk mode simulasi (tanpa hasil)
     */
    private function getSimulationViewData(): array
    {
        return [
            'periodMode' => 'simulation',
            'period' => ['mode' => 'simulation', 'label' => 'Simulasi', 'detail' => 'Masukkan data untuk simulasi', 'days_remaining' => 0, 'days_elapsed' => 0, 'total_days' => 30],
            'comparisonPeriod' => null,
            'isSimulation' => true,
            'current' => null,
            'previous' => null,
            'expenseRatio' => 0,
            'timeRatio' => 0,
            'totalBalance' => null,
            'totalBalanceNote' => 'Mode simulasi tidak menampilkan saldo total.',
            'totalExpense' => 0,
            'totalIncome' => 0,
            'dailyPatterns' => null,
            'dailyRecommendation' => null,
            'trend' => null,
            'composition' => null,
            'patterns' => null,
            'insights' => null,
            'warnings' => null,
            'healthScore' => null,
            'topCategories' => [],
            'walletSetting' => null,
            'analysis' => null,
            'simulationInput' => null,
        ];
    }

    /**
     * Get view data untuk error state
     */
    private function getErrorViewData(string $periodMode, string $errorMessage = ''): array
    {
        return [
            'periodMode' => $periodMode,
            'period' => ['mode' => $periodMode, 'label' => 'Error', 'detail' => 'Terjadi kesalahan', 'days_remaining' => 0, 'days_elapsed' => 0, 'total_days' => 0],
            'comparisonPeriod' => null,
            'isSimulation' => false,
            'current' => ['total_income' => 0, 'total_expense' => 0, 'balance' => 0, 'remaining_budget' => 0, 'transaction_count' => 0],
            'previous' => null,
            'expenseRatio' => 0,
            'timeRatio' => 0,
            'totalBalance' => 0,
            'totalBalanceNote' => '',
            'totalExpense' => 0,
            'totalIncome' => 0,
            'dailyPatterns' => ['average_daily' => 0, 'spending_consistency' => 'no_data'],
            'dailyRecommendation' => ['recommended_daily' => 0, 'current_daily_avg' => 0, 'remaining_budget' => 0, 'days_remaining' => 0, 'status' => 'no_data', 'status_label' => '-', 'category_recommendations' => []],
            'trend' => ['has_previous_data' => false],
            'composition' => [],
            'patterns' => [],
            'insights' => [[
                'type' => 'danger',
                'icon' => 'x-circle',
                'title' => 'Terjadi Kesalahan',
                'text' => 'Maaf, terjadi kendala saat memproses data keuanganmu. Silakan coba lagi nanti.',
            ]],
            'warnings' => [[
                'level' => 'info',
                'title' => 'Kesalahan Sistem',
                'message' => 'Tidak dapat memuat data analisis.',
                'action' => 'Coba refresh halaman ini.',
            ]],
            'healthScore' => ['score' => 0, 'label' => 'Error', 'color' => 'gray', 'breakdown' => []],
            'topCategories' => [],
            'walletSetting' => null,
            'analysis' => [],
            'simulationInput' => null,
        ];
    }

    /**
     * Display AI Chat interface
     */
    public function chat()
    {
        $user = auth()->user();

        // Get financial context for AI
        $financialContext = $this->aiService->getFinancialContext($user->id);

        // Get chat history from session
        $chatHistory = session('ai_chat_history', []);

        return view('ai.chat', [
            'financialContext' => $financialContext,
            'chatHistory' => $chatHistory,
        ]);
    }

    /**
     * Send message to AI and get response
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user = auth()->user();
        $userMessage = $request->input('message');

        // Handle clear history command
        if ($userMessage === '__clear_history__') {
            session()->forget('ai_chat_history');
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'response' => 'Chat history cleared.',
                ]);
            }
            return back();
        }

        try {
            // Get financial context
            $context = $this->aiService->getFinancialContext($user->id);

            // Get chat history for context
            $chatHistory = session('ai_chat_history', []);

            // Generate AI response
            $aiResponse = $this->aiService->chatWithAI($userMessage, $context, $chatHistory);

            // Store in session
            $chatHistory[] = [
                'role' => 'user',
                'content' => $userMessage,
                'timestamp' => now()->toISOString(),
            ];
            $chatHistory[] = [
                'role' => 'assistant',
                'content' => $aiResponse,
                'timestamp' => now()->toISOString(),
            ];

            // Keep only last 20 messages
            if (count($chatHistory) > 20) {
                $chatHistory = array_slice($chatHistory, -20);
            }

            session(['ai_chat_history' => $chatHistory]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'response' => $aiResponse,
                ]);
            }

            return back();

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'response' => 'Maaf, ada kendala saat memproses pesanmu. Silakan coba lagi.',
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim pesan ke AI');
        }
    }
}
