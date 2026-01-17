<?php

namespace App\Http\Controllers;

use App\Services\AIRecommendationService;
use Illuminate\Http\Request;

class AIRecommendationController extends Controller
{
    protected AIRecommendationService $aiService;

    public function __construct(AIRecommendationService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display AI recommendation page (legacy)
     */
    public function index()
    {
        $user = auth()->user();

        try {
            // Generate recommendation with defensive error handling
            $data = $this->aiService->generateRecommendation($user->id);

            return view('ai-recommendation', [
                'recommendation' => $data['recommendation'] ?? 'Belum ada data untuk dianalisis. Tambahkan beberapa transaksi terlebih dahulu.',
                'totalExpense' => $data['total_expense_7_days'] ?? 0,
                'categoryBreakdown' => $data['category_breakdown'] ?? collect([]),
                'categoryPercentages' => $data['category_percentages'] ?? [],
                'period' => $data['period'] ?? ['start' => '-', 'end' => '-'],
                'walletSetting' => $data['wallet_setting'] ?? null,
                'analysis' => $data['analysis'] ?? [],
            ]);
        } catch (\Exception $e) {
            // Fallback jika service error
            return view('ai-recommendation', [
                'recommendation' => 'Maaf, terjadi kendala saat memproses data keuanganmu. Silakan coba lagi nanti.',
                'totalExpense' => 0,
                'categoryBreakdown' => collect([]),
                'categoryPercentages' => [],
                'period' => ['start' => '-', 'end' => '-'],
                'walletSetting' => null,
                'analysis' => [],
            ]);
        }
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
                    'response' => 'Maaf, ada kendala saat memproses pesanmu. Coba lagi ya! 🙏',
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim pesan ke AI');
        }
    }
}
