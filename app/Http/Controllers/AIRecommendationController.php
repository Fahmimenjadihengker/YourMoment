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
     * Display AI recommendation page
     */
    public function index()
    {
        $user = auth()->user();

        // Generate recommendation
        $data = $this->aiService->generateRecommendation($user->id);

        return view('ai-recommendation', [
            'recommendation' => $data['recommendation'],
            'totalExpense' => $data['total_expense_7_days'],
            'categoryBreakdown' => $data['category_breakdown'],
            'categoryPercentages' => $data['category_percentages'],
            'period' => $data['period'],
            'walletSetting' => $data['wallet_setting'],
            'analysis' => $data['analysis'],
        ]);
    }
}
