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
     * Profesional, data-driven, tanpa emoji
     */
    private function buildFallbackText(array $summary): string
    {
        $income = $summary['total_income'];
        $expense = $summary['total_expense'];
        $savingRatio = $summary['saving_ratio'];
        $topCategory = $summary['top_category_name'];
        $topPercent = $summary['top_category_percent'];

        // Format angka
        $incomeFormatted = number_format($income, 0, ',', '.');
        $expenseFormatted = number_format($expense, 0, ',', '.');

        // Tidak ada transaksi
        if ($income == 0 && $expense == 0) {
            return "Belum ada transaksi tercatat untuk periode ini. Mulai catat pemasukan dan pengeluaranmu untuk mendapatkan analisis keuangan yang akurat.";
        }

        // Hanya ada pengeluaran, tidak ada pemasukan
        if ($income == 0 && $expense > 0) {
            return "Tercatat pengeluaran Rp{$expenseFormatted} namun belum ada pemasukan. Pastikan untuk mencatat semua sumber pemasukanmu agar analisis lebih lengkap.";
        }

        // Defisit (pengeluaran > pemasukan)
        if ($expense > $income) {
            $deficit = $expense - $income;
            $deficitFormatted = number_format($deficit, 0, ',', '.');
            $deficitPercent = $income > 0 ? round((($expense - $income) / $income) * 100) : 100;
            
            if ($deficitPercent > 50) {
                return "Pengeluaran (Rp{$expenseFormatted}) melebihi pemasukan dengan defisit Rp{$deficitFormatted}. Prioritaskan untuk mengurangi pengeluaran di kategori {$topCategory} yang mengambil {$topPercent}% dari total pengeluaran.";
            }
            return "Terjadi defisit Rp{$deficitFormatted} periode ini. Kategori {$topCategory} ({$topPercent}%) bisa menjadi fokus penghematan untuk menyeimbangkan keuangan.";
        }

        // Surplus tapi rasio tabungan sangat rendah (< 5%)
        if ($savingRatio < 5 && $savingRatio >= 0) {
            return "Pengeluaran hampir sama dengan pemasukan (rasio tabungan hanya {$savingRatio}%). Coba alokasikan minimal 10% dari pemasukan untuk tabungan. Kategori {$topCategory} ({$topPercent}%) bisa dievaluasi.";
        }

        // Surplus tapi rasio tabungan rendah (5-10%)
        if ($savingRatio >= 5 && $savingRatio < 10) {
            return "Keuangan cukup terkontrol dengan rasio tabungan {$savingRatio}%. Untuk kondisi yang lebih sehat, targetkan rasio tabungan 10-20%. Pengeluaran terbesar di {$topCategory} ({$topPercent}%).";
        }

        // Surplus dengan rasio tabungan sedang (10-20%)
        if ($savingRatio >= 10 && $savingRatio < 20) {
            return "Keuangan dalam kondisi baik dengan rasio tabungan {$savingRatio}%. Pengeluaran terbesar di kategori {$topCategory} ({$topPercent}%), pastikan sesuai prioritas kebutuhanmu.";
        }

        // Surplus dengan rasio tabungan baik (20-30%)
        if ($savingRatio >= 20 && $savingRatio < 30) {
            return "Pengelolaan keuangan sangat baik dengan rasio tabungan {$savingRatio}%. Pertahankan pola ini dan pertimbangkan untuk mengalokasikan tabungan ke tujuan finansial jangka panjang.";
        }

        // Surplus dengan rasio tabungan tinggi (>= 30%)
        if ($savingRatio >= 30) {
            return "Kondisi keuangan sangat sehat dengan rasio tabungan {$savingRatio}%. Pola pengeluaran sudah efisien dengan pos terbesar di {$topCategory} ({$topPercent}%). Pertahankan konsistensi ini.";
        }

        // Default fallback
        return "Keuangan periode ini tercatat dengan total pemasukan Rp{$incomeFormatted} dan pengeluaran Rp{$expenseFormatted}. Kategori {$topCategory} menjadi pos pengeluaran terbesar ({$topPercent}%).";
    }
}
