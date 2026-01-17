<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AIService - Komunikasi dengan OpenAI API
 * 
 * Service ini menangani:
 * - Pengiriman prompt ke OpenAI
 * - Fallback jika API error
 * - Error handling
 */
class AIService
{
    protected string $apiKey;
    protected string $model = 'gpt-4o-mini';
    protected float $temperature = 0.6;
    protected int $maxTokens = 200;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
    }

    /**
     * Generate insight dari data summary keuangan
     * 
     * @param array $summary Data dari InsightService::generateSummary()
     * @return array ['success' => bool, 'insight' => string, 'source' => 'ai'|'fallback']
     */
    public function generateInsight(array $summary): array
    {
        // Jika API key tidak tersedia, langsung fallback
        if (empty($this->apiKey)) {
            Log::info('AIService: API key not available, using fallback');
            return $this->generateFallbackInsight($summary);
        }

        try {
            $prompt = $this->buildPrompt($summary);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah asisten analis keuangan pribadi yang membantu mahasiswa mengelola keuangan.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $insight = $data['choices'][0]['message']['content'] ?? null;

                if ($insight) {
                    return [
                        'success' => true,
                        'insight' => trim($insight),
                        'source' => 'ai',
                    ];
                }
            }

            // Jika response tidak sukses, log dan fallback
            Log::warning('AIService: API response not successful', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $this->generateFallbackInsight($summary);

        } catch (\Exception $e) {
            Log::error('AIService: Exception occurred', [
                'message' => $e->getMessage()
            ]);

            return $this->generateFallbackInsight($summary);
        }
    }

    /**
     * Build prompt sesuai spesifikasi
     */
    private function buildPrompt(array $summary): string
    {
        $income = number_format($summary['total_income'], 0, ',', '.');
        $expense = number_format($summary['total_expense'], 0, ',', '.');
        
        return "Kamu adalah asisten analis keuangan pribadi untuk aplikasi YourMoMent.
Tugasmu adalah menjelaskan kondisi keuangan pengguna secara singkat, jelas, dan solutif berdasarkan data ringkasan yang diberikan.
Aturan penting:
- Gunakan bahasa Indonesia yang santai, sopan, dan mudah dipahami mahasiswa
- Jangan menghakimi
- Maksimal 3 kalimat
- Jangan membuat asumsi di luar data
- Jangan menyebut kata AI atau model
Data keuangan periode {$summary['period_label']}:
- Total pemasukan: Rp{$income}
- Total pengeluaran: Rp{$expense}
- Status saldo: {$summary['balance_status']}
- Kategori pengeluaran terbesar: {$summary['top_category_name']} ({$summary['top_category_percent']}%)
- Rasio tabungan: {$summary['saving_ratio']}%
Buatkan 1 insight utama.";
    }

    /**
     * Generate fallback insight berbasis rule (if-else)
     * Digunakan jika API error, timeout, atau tidak tersedia
     */
    public function generateFallbackInsight(array $summary): array
    {
        $insight = $this->buildFallbackText($summary);

        return [
            'success' => true,
            'insight' => $insight,
            'source' => 'fallback',
        ];
    }

    /**
     * Build teks fallback berdasarkan kondisi keuangan
     */
    private function buildFallbackText(array $summary): string
    {
        $income = $summary['total_income'];
        $expense = $summary['total_expense'];
        $savingRatio = $summary['saving_ratio'];
        $topCategory = $summary['top_category_name'];
        $topPercent = $summary['top_category_percent'];

        // Tidak ada transaksi
        if ($income == 0 && $expense == 0) {
            return "Belum ada catatan transaksi bulan ini. Yuk mulai catat pemasukan dan pengeluaranmu untuk mendapat insight yang lebih akurat! 📝";
        }

        // Defisit (pengeluaran > pemasukan)
        if ($expense > $income) {
            $deficit = $expense - $income;
            $deficitFormatted = number_format($deficit, 0, ',', '.');
            return "Pengeluaran bulan ini lebih besar dari pemasukan (defisit Rp{$deficitFormatted}). Coba kurangi pengeluaran di kategori {$topCategory} yang mengambil {$topPercent}% dari total pengeluaranmu. Semangat mengatur keuangan! 💪";
        }

        // Surplus tapi rasio tabungan rendah (< 10%)
        if ($savingRatio < 10 && $savingRatio >= 0) {
            return "Keuanganmu bulan ini cukup terkontrol, tapi rasio tabungan masih {$savingRatio}%. Coba sisihkan sedikit lebih banyak untuk tabungan ya! Pengeluaran terbesar di {$topCategory} ({$topPercent}%). 🎯";
        }

        // Surplus dengan rasio tabungan sedang (10-30%)
        if ($savingRatio >= 10 && $savingRatio < 30) {
            return "Bagus! Kamu berhasil menyisihkan {$savingRatio}% dari pemasukanmu bulan ini. Pengeluaran terbesar ada di {$topCategory}, pastikan tetap sesuai kebutuhan ya! 👍";
        }

        // Surplus dengan rasio tabungan tinggi (>= 30%)
        if ($savingRatio >= 30) {
            return "Luar biasa! Rasio tabunganmu bulan ini {$savingRatio}%, sangat baik untuk kondisi keuangan jangka panjang. Pertahankan pola pengeluaran yang sudah bagus ini! 🌟";
        }

        // Default fallback
        return "Keuanganmu bulan ini tercatat dengan baik. Terus pantau pengeluaran di kategori {$topCategory} yang menjadi pos terbesar ({$topPercent}%). Semangat mengelola keuangan! 💰";
    }
}
