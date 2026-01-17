<?php

namespace App\Services;

/**
 * GoalSimulationService
 * 
 * Service untuk menghitung simulasi target finansial secara REALISTIS.
 * 
 * Flow:
 * - Input: targetAmount, monthlyAllowance (uang jajan total)
 * - Hitung essential cost (dari data real atau default 65%)
 * - monthlySaving = monthlyAllowance - essentialCost
 * - duration = ceil(targetAmount / monthlySaving)
 */
class GoalSimulationService
{
    /**
     * Default percentage untuk essential spending jika tidak ada data
     */
    protected const DEFAULT_ESSENTIAL_PERCENTAGE = 0.65;

    /**
     * Kategori yang dianggap essential/wajib
     */
    protected array $essentialCategories = [
        'makan',
        'food',
        'makanan',
        'transport',
        'transportasi',
        'akademik',
        'pendidikan',
        'education',
        'kesehatan',
        'health',
    ];

    /**
     * Pesan pembuka yang bervariasi
     */
    protected array $openingMessages = [
        "🎯 Aku sudah hitung simulasinya secara realistis!",
        "🧮 Oke, aku bantu hitung dengan asumsi realistis ya!",
        "📊 Berdasarkan perhitungan realistis:",
        "✨ Sip, ini hasil simulasi yang lebih akurat:",
        "💡 Ini perhitungan yang lebih masuk akal:",
    ];

    /**
     * Pesan motivasi untuk durasi pendek (≤3 bulan)
     */
    protected array $shortDurationMessages = [
        "Wah, cepat banget! Tinggal konsisten aja ya! 💪",
        "Keren! Targetmu realistis dan achievable! 🎉",
        "Mantap! Dalam waktu singkat bisa tercapai! ⚡",
        "Gas terus! Sebentar lagi goal-mu tercapai! 🚀",
    ];

    /**
     * Pesan motivasi untuk durasi medium (4-6 bulan)
     */
    protected array $mediumDurationMessages = [
        "Lumayan! Yang penting konsisten ya! 💪",
        "Setengah tahun-an, masih reasonable! Keep it up! 🌟",
        "Bisa banget! Asal rajin nabung pasti tercapai! ✨",
        "Timeframe-nya realistis! Semangat terus! 🎯",
    ];

    /**
     * Pesan motivasi untuk durasi panjang (7-12 bulan)
     */
    protected array $longDurationMessages = [
        "Butuh kesabaran, tapi pasti worth it! 🌈",
        "Sekitar setahun, yang penting jangan menyerah! 💪",
        "Perjalanan panjang dimulai dari langkah kecil! 🚶",
        "Slow but steady wins the race! Tetap semangat! 🐢✨",
    ];

    /**
     * Pesan motivasi untuk durasi sangat panjang (>12 bulan)
     */
    protected array $veryLongDurationMessages = [
        "Butuh waktu cukup lama nih. Ada beberapa opsi yang bisa dipertimbangkan! 🤔",
        "Long journey ahead! Mungkin perlu strategi tambahan? 💡",
        "Waktunya lumayan panjang. Yuk kita lihat alternatifnya! 🌟",
    ];

    /**
     * Tips tambahan berdasarkan situasi
     */
    protected array $savingTips = [
        "💡 **Tip:** Sisihkan uang tabungan di awal bulan, bukan sisa akhir bulan!",
        "💡 **Tip:** Buat rekening terpisah khusus untuk goal ini biar nggak kepakai!",
        "💡 **Tip:** Track progress mingguan biar tetap termotivasi!",
        "💡 **Tip:** Kalau ada rezeki lebih, langsung masukkan ke tabungan goal ini!",
        "💡 **Tip:** Challenge diri sendiri untuk no-spend day seminggu sekali!",
        "💡 **Tip:** Cari side income untuk mempercepat pencapaian target!",
    ];

    /**
     * Simulate goal achievement - REALISTIC VERSION
     * 
     * @param int $targetAmount Target yang ingin dicapai (dalam Rupiah)
     * @param int $monthlyAllowance Total uang jajan/income per bulan
     * @param array|null $categoryBreakdown Data pengeluaran per kategori (optional)
     * @return string Response untuk user
     */
    public function simulate(int $targetAmount, int $monthlyAllowance, ?array $categoryBreakdown = null): string
    {
        // Validasi input
        if ($targetAmount <= 0) {
            return "🤔 Hmm, target-nya belum jelas nih. Coba sebutkan nominal target yang kamu inginkan ya!";
        }

        if ($monthlyAllowance <= 0) {
            return "🤔 Berapa uang jajan atau penghasilan bulananmu? Kasih tau dong biar aku bisa hitung realistis!";
        }

        // Hitung essential cost
        $essentialCost = $this->calculateEssentialCost($monthlyAllowance, $categoryBreakdown);

        // Hitung realistic monthly saving
        $monthlySaving = $monthlyAllowance - $essentialCost;

        // Format angka
        $targetFormatted = $this->formatRupiah($targetAmount);
        $allowanceFormatted = $this->formatRupiah($monthlyAllowance);
        $essentialFormatted = $this->formatRupiah($essentialCost);
        $savingFormatted = $this->formatRupiah(max(0, $monthlySaving));

        // Handle case: tidak bisa menabung
        if ($monthlySaving <= 0) {
            return $this->buildCannotSaveResponse($targetFormatted, $allowanceFormatted, $essentialFormatted);
        }

        // Hitung durasi dalam bulan
        $months = (int) ceil($targetAmount / $monthlySaving);

        // Build response
        $response = $this->getRandomMessage($this->openingMessages) . "\n\n";

        // Breakdown perhitungan
        $response .= "💵 **Uang jajan/bulan:** {$allowanceFormatted}\n";
        $response .= "🛒 **Estimasi kebutuhan wajib:** {$essentialFormatted}";

        // Tambah info sumber data
        if ($categoryBreakdown !== null && !empty($categoryBreakdown)) {
            $response .= " *(dari data transaksimu)*\n";
        } else {
            $response .= " *(estimasi 65%)*\n";
        }

        $response .= "💰 **Realistis bisa ditabung:** {$savingFormatted}/bulan\n\n";

        // Main result
        $response .= "🎯 **Target:** {$targetFormatted}\n";
        $response .= "⏱️ **Estimasi waktu:** ";

        if ($months == 1) {
            $response .= "**1 bulan** saja!\n\n";
        } elseif ($months < 12) {
            $response .= "**{$months} bulan**\n\n";
        } else {
            $years = floor($months / 12);
            $remainingMonths = $months % 12;
            if ($remainingMonths == 0) {
                $response .= "**{$years} tahun**\n\n";
            } else {
                $response .= "**{$years} tahun {$remainingMonths} bulan**\n\n";
            }
        }

        // Add motivational message based on duration
        $response .= $this->getMotivationalMessage($months) . "\n\n";

        // Add alternative scenarios for long duration
        if ($months > 12) {
            $response .= $this->getAlternativeScenarios($targetAmount, $monthlySaving, $months);
        }

        // Add progress breakdown if useful
        if ($months > 1 && $months <= 24) {
            $response .= $this->getProgressBreakdown($targetAmount, $monthlySaving, $months);
        }

        // Add random tip
        $response .= "\n" . $this->getRandomMessage($this->savingTips);

        return $response;
    }

    /**
     * Simulate dengan weekly allowance
     */
    public function simulateWeekly(int $targetAmount, int $weeklyAllowance, ?array $categoryBreakdown = null): string
    {
        // Convert to monthly (approximately 4.33 weeks per month)
        $monthlyAllowance = (int) ($weeklyAllowance * 4.33);
        return $this->simulate($targetAmount, $monthlyAllowance, $categoryBreakdown);
    }

    /**
     * Calculate essential cost dari category breakdown atau default
     */
    protected function calculateEssentialCost(int $monthlyAllowance, ?array $categoryBreakdown): int
    {
        // Jika ada data kategori, hitung dari data real
        if ($categoryBreakdown !== null && !empty($categoryBreakdown)) {
            $essentialTotal = 0;

            foreach ($categoryBreakdown as $category) {
                $categoryName = strtolower($category['name'] ?? '');

                // Cek apakah kategori ini essential
                foreach ($this->essentialCategories as $essential) {
                    if (strpos($categoryName, $essential) !== false) {
                        $essentialTotal += (int) ($category['total'] ?? 0);
                        break;
                    }
                }
            }

            // Jika ada data essential, gunakan itu
            // Minimal 40% dari allowance untuk safety
            if ($essentialTotal > 0) {
                return max($essentialTotal, (int) ($monthlyAllowance * 0.4));
            }
        }

        // Default: 65% dari uang jajan untuk kebutuhan wajib
        return (int) ($monthlyAllowance * self::DEFAULT_ESSENTIAL_PERCENTAGE);
    }

    /**
     * Build response ketika user tidak bisa menabung
     */
    protected function buildCannotSaveResponse(string $target, string $allowance, string $essential): string
    {
        $response = "⚠️ **Hmm, ada kendala nih...**\n\n";
        $response .= "💵 Uang jajan/bulan: {$allowance}\n";
        $response .= "🛒 Estimasi kebutuhan wajib: {$essential}\n\n";
        $response .= "Dengan kondisi ini, sulit untuk menabung karena kebutuhan wajib sudah menghabiskan semua uang jajan.\n\n";
        $response .= "**Saran:**\n";
        $response .= "1. 💰 Coba cari penghasilan tambahan (freelance, part-time)\n";
        $response .= "2. ✂️ Review pengeluaran - mungkin ada yang bisa dikurangi?\n";
        $response .= "3. 🎯 Atau turunkan target dulu ke nominal yang lebih realistis\n\n";
        $response .= "Mau aku bantu analisis pengeluaranmu? Ketik \"analisis pengeluaran\" 💡";

        return $response;
    }

    /**
     * Get alternative scenarios untuk durasi panjang
     */
    protected function getAlternativeScenarios(int $target, int $currentSaving, int $currentMonths): string
    {
        $response = "📋 **Alternatif untuk mempercepat:**\n";

        // Scenario 1: Tambah 25% tabungan
        $scenario1Saving = (int) ($currentSaving * 1.25);
        $scenario1Months = (int) ceil($target / $scenario1Saving);
        $scenario1Formatted = $this->formatRupiah($scenario1Saving);

        // Scenario 2: Tambah 50% tabungan
        $scenario2Saving = (int) ($currentSaving * 1.5);
        $scenario2Months = (int) ceil($target / $scenario2Saving);
        $scenario2Formatted = $this->formatRupiah($scenario2Saving);

        $response .= "• Nabung {$scenario1Formatted}/bulan → **{$scenario1Months} bulan**\n";
        $response .= "• Nabung {$scenario2Formatted}/bulan → **{$scenario2Months} bulan**\n\n";

        return $response;
    }

    /**
     * Get motivational message based on duration
     */
    protected function getMotivationalMessage(int $months): string
    {
        if ($months <= 3) {
            return $this->getRandomMessage($this->shortDurationMessages);
        } elseif ($months <= 6) {
            return $this->getRandomMessage($this->mediumDurationMessages);
        } elseif ($months <= 12) {
            return $this->getRandomMessage($this->longDurationMessages);
        } else {
            return $this->getRandomMessage($this->veryLongDurationMessages);
        }
    }

    /**
     * Get progress breakdown for visualization
     */
    protected function getProgressBreakdown(int $target, int $monthly, int $totalMonths): string
    {
        $breakdown = "📈 **Progress Estimasi:**\n";

        // Show milestones at 25%, 50%, 75%, 100%
        $milestones = [25, 50, 75, 100];

        foreach ($milestones as $percentage) {
            $amountAtMilestone = (int) (($target * $percentage) / 100);
            $monthsToReach = (int) ceil($amountAtMilestone / $monthly);

            if ($monthsToReach <= $totalMonths) {
                $amountFormatted = $this->formatRupiah($amountAtMilestone);
                $breakdown .= "• Bulan ke-{$monthsToReach}: {$amountFormatted} ({$percentage}%)\n";
            }
        }

        return $breakdown;
    }

    /**
     * Format number to Rupiah
     */
    protected function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get random message from array
     */
    protected function getRandomMessage(array $messages): string
    {
        return $messages[array_rand($messages)];
    }

    /**
     * Kata kunci yang menunjukkan nama barang/item target
     */
    protected static array $itemKeywords = [
        'laptop',
        'hp',
        'handphone',
        'pc',
        'komputer',
        'computer',
        'motor',
        'mobil',
        'iphone',
        'smartphone',
        'gadget',
        'kamera',
        'camera',
        'tablet',
        'ipad',
        'macbook',
        'ps5',
        'playstation',
        'xbox',
        'console',
        'sepeda',
        'jam tangan',
        'jam',
        'watch',
        'tas',
        'bag',
        'sepatu',
        'shoes',
        'tv',
        'television',
        'monitor',
        'keyboard',
        'mouse',
        'headphone',
        'earphone',
        'airpods',
        'speaker',
        'drone',
    ];

    /**
     * Extract MULTIPLE targets dari message menggunakan GLOBAL MATCH
     * 
     * Contoh:
     * - "laptop 7jt dan hp 4jt" → [['name'=>'laptop','amount'=>7000000], ['name'=>'hp','amount'=>4000000]]
     * - "ipad 7jt dan macbook m3 pro 20jt" → [['name'=>'ipad','amount'=>7000000], ['name'=>'macbook m3 pro','amount'=>20000000]]
     * 
     * @param string $message
     * @return array Array of targets with 'name' and 'amount'
     */
    public static function extractMultipleTargets(string $message): array
    {
        $message = strtolower($message);
        $targets = [];
        $foundPositions = []; // Track positions to avoid duplicates

        // =====================================================
        // STRATEGY 1: Match item keyword + optional modifier + nominal
        // Pattern: (item_keyword)(optional_words)(nominal)
        // Example: "macbook m3 pro 20jt", "ipad 7jt"
        // =====================================================

        // Build regex pattern for all item keywords
        $itemKeywordsPattern = implode('|', array_map(function ($item) {
            return preg_quote($item, '/');
        }, self::$itemKeywords));

        // Pattern yang menangkap: item + optional modifier (max 5 words) + nominal
        // Contoh: "macbook m3 pro 20jt", "ipad mini 6 8jt", "laptop asus 15jt"
        $pattern = '/\b(' . $itemKeywordsPattern . ')(\s+[\w\-]+){0,5}?\s*(\d+(?:[.,]\d+)?)\s*(jt|juta|rb|ribu|k)/i';

        if (preg_match_all($pattern, $message, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                $itemKeyword = $match[1][0]; // Base item keyword
                $modifier = isset($match[2][0]) ? trim($match[2][0]) : ''; // Optional modifier
                $number = $match[3][0]; // The number
                $unit = $match[4][0]; // jt/juta/rb/ribu/k
                $position = $match[0][1]; // Position in string

                // Skip if we already have a target at nearby position (within 5 chars)
                $isDuplicate = false;
                foreach ($foundPositions as $pos) {
                    if (abs($position - $pos) < 5) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if ($isDuplicate) continue;

                // Build full item name
                $itemName = $itemKeyword;
                if (!empty($modifier)) {
                    // Clean up modifier - remove income-related words
                    $modifier = preg_replace('/\b(gaji|penghasilan|uang|jajan|income|per|bulan|sebulan)\b/i', '', $modifier);
                    $modifier = trim(preg_replace('/\s+/', ' ', $modifier));
                    if (!empty($modifier)) {
                        $itemName .= ' ' . $modifier;
                    }
                }

                // Parse nominal
                $nominalString = $number . $unit;
                $amount = self::parseNominal($nominalString);

                if ($amount !== null && $amount > 0) {
                    $targets[] = [
                        'name' => trim($itemName),
                        'amount' => $amount,
                    ];
                    $foundPositions[] = $position;
                }
            }
        }

        // =====================================================
        // STRATEGY 2: Fallback - simpler pattern per item keyword
        // Jika strategy 1 tidak menemukan, coba per keyword
        // =====================================================

        if (empty($targets)) {
            foreach (self::$itemKeywords as $item) {
                // Simple pattern: item + up to 20 chars + nominal
                $pattern = '/\b' . preg_quote($item, '/') . '\b(.{0,20}?)(\d+(?:[.,]\d+)?)\s*(jt|juta|rb|ribu|k)/i';

                if (preg_match_all($pattern, $message, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $fullMatch = $match[0];
                        $amount = self::parseNominal($fullMatch);

                        if ($amount !== null && $amount > 0) {
                            // Check for modifier between item and number
                            $modifier = trim($match[1]);
                            $modifier = preg_replace('/\b(harga|seharga|senilai|sekitar|dan|dengan)\b/i', '', $modifier);
                            $modifier = trim($modifier);

                            $itemName = $item;
                            if (!empty($modifier) && strlen($modifier) < 20) {
                                $itemName .= ' ' . $modifier;
                            }

                            $targets[] = [
                                'name' => trim($itemName),
                                'amount' => $amount,
                            ];
                        }
                    }
                }
            }
        }

        // =====================================================
        // STRATEGY 3: Ultimate fallback - use extractAmounts
        // =====================================================

        if (empty($targets)) {
            $amounts = self::extractAmounts($message);
            if ($amounts['target'] !== null) {
                $itemName = self::extractItemNameFromContext($message);
                $targets[] = [
                    'name' => $itemName ?? 'target',
                    'amount' => $amounts['target'],
                ];
            }
        }

        // =====================================================
        // Remove duplicates based on similar names
        // =====================================================

        $uniqueTargets = [];
        $seenNames = [];
        foreach ($targets as $target) {
            // Normalize name for comparison
            $normalizedName = preg_replace('/\s+/', '', strtolower($target['name']));

            // Check if we already have similar name
            $isDuplicate = false;
            foreach ($seenNames as $seenName) {
                // Check if one contains the other (e.g., "macbook" vs "macbook m3 pro")
                if (strpos($normalizedName, $seenName) !== false || strpos($seenName, $normalizedName) !== false) {
                    // Keep the longer (more specific) name
                    if (strlen($normalizedName) > strlen($seenName)) {
                        // Replace the shorter one
                        foreach ($uniqueTargets as $key => $ut) {
                            if (preg_replace('/\s+/', '', strtolower($ut['name'])) === $seenName) {
                                $uniqueTargets[$key] = $target;
                                $seenNames[array_search($seenName, $seenNames)] = $normalizedName;
                                break;
                            }
                        }
                    }
                    $isDuplicate = true;
                    break;
                }
            }

            if (!$isDuplicate) {
                $uniqueTargets[] = $target;
                $seenNames[] = $normalizedName;
            }
        }

        return $uniqueTargets;
    }

    /**
     * Extract item name dari context message
     */
    protected static function extractItemNameFromContext(string $message): ?string
    {
        foreach (self::$itemKeywords as $item) {
            if (strpos($message, $item) !== false) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Calculate total dari multiple targets
     */
    public static function calculateTotalTarget(array $targets): int
    {
        return array_sum(array_column($targets, 'amount'));
    }

    /**
     * Format multiple targets untuk display
     */
    public static function formatTargetsForDisplay(array $targets): string
    {
        if (empty($targets)) {
            return '';
        }

        $items = [];
        foreach ($targets as $target) {
            $amount = 'Rp ' . number_format($target['amount'], 0, ',', '.');
            $name = ucfirst($target['name']);
            $items[] = "• **{$name}**: {$amount}";
        }

        return implode("\n", $items);
    }

    /**
     * Simulate goal achievement for MULTIPLE targets
     * 
     * @param array $targets Array of targets [['name' => 'laptop', 'amount' => 7000000], ...]
     * @param int $monthlyAllowance Total uang jajan/income per bulan
     * @param array|null $categoryBreakdown Data pengeluaran per kategori
     * @return string Response untuk user
     */
    public function simulateMultipleTargets(array $targets, int $monthlyAllowance, ?array $categoryBreakdown = null): string
    {
        // Validasi input
        if (empty($targets)) {
            return "🤔 Hmm, target-nya belum jelas nih. Coba sebutkan barang yang kamu inginkan ya!";
        }

        if ($monthlyAllowance <= 0) {
            return "🤔 Berapa uang jajan atau penghasilan bulananmu? Kasih tau dong biar aku bisa hitung realistis!";
        }

        // Hitung total target
        $totalTarget = self::calculateTotalTarget($targets);

        // Jika hanya 1 target, gunakan simulate biasa
        if (count($targets) === 1) {
            return $this->simulate($totalTarget, $monthlyAllowance, $categoryBreakdown);
        }

        // Multiple targets - special response
        $essentialCost = $this->calculateEssentialCost($monthlyAllowance, $categoryBreakdown);
        $monthlySaving = $monthlyAllowance - $essentialCost;

        // Format angka
        $totalFormatted = $this->formatRupiah($totalTarget);
        $allowanceFormatted = $this->formatRupiah($monthlyAllowance);
        $essentialFormatted = $this->formatRupiah($essentialCost);
        $savingFormatted = $this->formatRupiah(max(0, $monthlySaving));

        // Handle case: tidak bisa menabung
        if ($monthlySaving <= 0) {
            return $this->buildCannotSaveResponse($totalFormatted, $allowanceFormatted, $essentialFormatted);
        }

        // Hitung durasi
        $months = (int) ceil($totalTarget / $monthlySaving);

        // Build response
        $response = "🎯 **Simulasi Multi Target!**\n\n";
        $response .= "Kamu ingin membeli:\n";
        $response .= self::formatTargetsForDisplay($targets) . "\n\n";
        $response .= "📊 **Total Target:** {$totalFormatted}\n\n";

        // Breakdown perhitungan
        $response .= "💵 **Uang jajan/bulan:** {$allowanceFormatted}\n";
        $response .= "🛒 **Estimasi kebutuhan wajib:** {$essentialFormatted}";

        if ($categoryBreakdown !== null && !empty($categoryBreakdown)) {
            $response .= " *(dari data transaksimu)*\n";
        } else {
            $response .= " *(estimasi 65%)*\n";
        }

        $response .= "💰 **Realistis bisa ditabung:** {$savingFormatted}/bulan\n\n";

        // Main result
        $response .= "⏱️ **Estimasi waktu untuk SEMUA target:** ";

        if ($months == 1) {
            $response .= "**1 bulan** saja!\n\n";
        } elseif ($months < 12) {
            $response .= "**{$months} bulan**\n\n";
        } else {
            $years = floor($months / 12);
            $remainingMonths = $months % 12;
            if ($remainingMonths == 0) {
                $response .= "**{$years} tahun**\n\n";
            } else {
                $response .= "**{$years} tahun {$remainingMonths} bulan**\n\n";
            }
        }

        // Timeline per item
        $response .= "📅 **Timeline per item:**\n";
        $runningTotal = 0;
        foreach ($targets as $target) {
            $runningTotal += $target['amount'];
            $itemMonths = (int) ceil($runningTotal / $monthlySaving);
            $name = ucfirst($target['name']);
            $response .= "• {$name}: bulan ke-{$itemMonths}\n";
        }
        $response .= "\n";

        // Add motivational message
        $response .= $this->getMotivationalMessage($months) . "\n\n";

        // Add tip
        $response .= $this->getRandomMessage($this->savingTips);

        return $response;
    }

    /**
     * Parse nominal dari teks user
     * Contoh: "10jt" → 10000000, "500rb" → 500000, "2 juta" → 2000000
     * 
     * @param string $text
     * @return int|null
     */
    public static function parseNominal(string $text): ?int
    {
        $text = strtolower(trim($text));

        // Pattern untuk menangkap angka dengan satuan
        $patterns = [
            // 10jt, 10 jt, 10juta, 10 juta, 10 jt-an
            '/(\d+(?:[.,]\d+)?)\s*(?:jt|juta|jt-an|juta-an)/i' => 1000000,
            // 500rb, 500 rb, 500ribu, 500 ribu, 500rb-an
            '/(\d+(?:[.,]\d+)?)\s*(?:rb|ribu|rb-an|ribu-an|k)/i' => 1000,
            // 1m, 1 m (million) - less common in Indonesian
            '/(\d+(?:[.,]\d+)?)\s*m(?:illion)?/i' => 1000000,
            // Plain number (assume rupiah) - only if > 10000
            '/(\d{5,})/' => 1,
        ];

        foreach ($patterns as $pattern => $multiplier) {
            if (preg_match($pattern, $text, $matches)) {
                // Handle comma/dot as decimal separator
                $number = str_replace(',', '.', $matches[1]);
                $value = (float) $number * $multiplier;
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Extract target dan monthly allowance dari message BERDASARKAN KONTEKS KATA KUNCI
     * 
     * RULE:
     * - Kata kunci TARGET: harga, target, budget, beli, rakit, ingin, mau, pengen, seharga, senilai
     *   → angka setelah kata kunci ini = targetAmount
     * 
     * - Kata kunci ALLOWANCE: uang jajan, per bulan, sebulan, gaji, penghasilan, allowance
     *   → angka setelah kata kunci ini = monthlyAllowance
     * 
     * - Kata kunci WEEKLY: per minggu, seminggu, mingguan
     *   → angka setelah kata kunci ini = weeklyAllowance
     * 
     * - FALLBACK (jika context tidak ditemukan):
     *   → angka pertama = target, angka kedua = allowance (urutan dalam kalimat)
     * 
     * @param string $message
     * @return array ['target' => int|null, 'monthly' => int|null, 'weekly' => int|null]
     */
    public static function extractAmounts(string $message): array
    {
        $message = strtolower($message);
        $result = [
            'target' => null,
            'monthly' => null,
            'weekly' => null,
        ];

        // =====================================================
        // STEP 1: Extract semua nominal dengan posisi dalam teks
        // =====================================================
        $moneyPattern = '/(\d+(?:[.,]\d+)?)\s*(?:jt|juta|rb|ribu|k|m(?:illion)?)/i';
        preg_match_all($moneyPattern, $message, $allMatches, PREG_OFFSET_CAPTURE);

        if (empty($allMatches[0])) {
            return $result;
        }

        // Build array of amounts with their positions
        $amountsWithPos = [];
        foreach ($allMatches[0] as $match) {
            $amountsWithPos[] = [
                'raw' => $match[0],
                'value' => self::parseNominal($match[0]),
                'position' => $match[1],
            ];
        }

        // =====================================================
        // STEP 2: Define kata kunci dengan posisi prioritas
        // =====================================================

        // Kata kunci untuk TARGET (harga barang/goal)
        $targetKeywords = [
            'harga',
            'seharga',
            'senilai',
            'target',
            'budget',
            'beli',
            'rakit',
            'ingin beli',
            'mau beli',
            'pengen beli',
            'ingin rakit',
            'mau rakit',
            'pengen rakit',
            'ingin punya',
            'mau punya',
            'laptop',
            'hp',
            'pc',
            'motor',
            'mobil',
            'iphone',
            'handphone',
            'komputer',
            'gadget',
        ];

        // Kata kunci untuk MONTHLY ALLOWANCE (uang bulanan)
        $monthlyKeywords = [
            'uang jajan sebulan',
            'uang jajan perbulan',
            'uang jajan per bulan',
            'uang jajan bulanan',
            'jajan sebulan',
            'jajan perbulan',
            'jajan per bulan',
            'uang jajan',
            'gaji sebulan',
            'gaji perbulan',
            'gaji per bulan',
            'gaji bulanan',
            'gaji',
            'penghasilan sebulan',
            'penghasilan perbulan',
            'penghasilan per bulan',
            'penghasilan bulanan',
            'penghasilan',
            'income sebulan',
            'income perbulan',
            'income per bulan',
            'income bulanan',
            'income',
            'allowance sebulan',
            'allowance perbulan',
            'allowance per bulan',
            'allowance bulanan',
            'allowance',
            'per bulan',
            'perbulan',
            'sebulan',
            '/bulan',
            'bulanan',
            'tiap bulan',
            'setiap bulan',
        ];

        // Kata kunci untuk WEEKLY ALLOWANCE (uang mingguan)
        $weeklyKeywords = [
            'uang jajan seminggu',
            'uang jajan perminggu',
            'uang jajan per minggu',
            'uang jajan mingguan',
            'jajan seminggu',
            'jajan perminggu',
            'jajan per minggu',
            'per minggu',
            'perminggu',
            'seminggu',
            '/minggu',
            'mingguan',
            'tiap minggu',
            'setiap minggu',
        ];

        // =====================================================
        // STEP 3: Cari nominal yang associated dengan keywords
        // =====================================================

        // Untuk setiap nominal, cek kata kunci mana yang paling dekat SEBELUMNYA
        foreach ($amountsWithPos as &$amount) {
            $amount['context'] = self::findNearestKeywordContext(
                $message,
                $amount['position'],
                $targetKeywords,
                $monthlyKeywords,
                $weeklyKeywords
            );
        }
        unset($amount);

        // =====================================================
        // STEP 4: Assign nilai berdasarkan konteks
        // =====================================================

        // First pass: assign berdasarkan context yang terdeteksi
        foreach ($amountsWithPos as $amount) {
            switch ($amount['context']) {
                case 'target':
                    if ($result['target'] === null) {
                        $result['target'] = $amount['value'];
                    }
                    break;
                case 'monthly':
                    if ($result['monthly'] === null) {
                        $result['monthly'] = $amount['value'];
                    }
                    break;
                case 'weekly':
                    if ($result['weekly'] === null) {
                        $result['weekly'] = $amount['value'];
                    }
                    break;
            }
        }

        // =====================================================
        // STEP 5: FALLBACK - jika context tidak ditemukan
        // =====================================================

        // Jika masih ada yang null dan ada nominal tanpa context, gunakan urutan
        $unassigned = array_filter($amountsWithPos, fn($a) => $a['context'] === null);

        if (!empty($unassigned)) {
            // Sort by position (urutan dalam kalimat)
            usort($unassigned, fn($a, $b) => $a['position'] <=> $b['position']);

            foreach ($unassigned as $amount) {
                // Assign ke slot pertama yang kosong
                // Prioritas: target dulu, lalu monthly
                if ($result['target'] === null) {
                    $result['target'] = $amount['value'];
                } elseif ($result['monthly'] === null) {
                    $result['monthly'] = $amount['value'];
                } elseif ($result['weekly'] === null) {
                    $result['weekly'] = $amount['value'];
                }
            }
        }

        return $result;
    }

    /**
     * Cari konteks kata kunci terdekat SEBELUM posisi nominal
     * 
     * @param string $message Full message
     * @param int $nominalPosition Posisi nominal dalam string
     * @param array $targetKeywords
     * @param array $monthlyKeywords
     * @param array $weeklyKeywords
     * @return string|null 'target', 'monthly', 'weekly', atau null
     */
    protected static function findNearestKeywordContext(
        string $message,
        int $nominalPosition,
        array $targetKeywords,
        array $monthlyKeywords,
        array $weeklyKeywords
    ): ?string {
        $textBefore = substr($message, 0, $nominalPosition);

        // Ambil maksimal 50 karakter sebelum nominal untuk dicari keyword-nya
        $searchWindow = 80;
        $startPos = max(0, strlen($textBefore) - $searchWindow);
        $relevantText = substr($textBefore, $startPos);

        $nearestKeyword = null;
        $nearestDistance = PHP_INT_MAX;
        $nearestType = null;

        // Helper function untuk cari posisi keyword terakhir
        $findLastKeyword = function ($keywords, $type) use ($relevantText, &$nearestKeyword, &$nearestDistance, &$nearestType) {
            foreach ($keywords as $keyword) {
                $pos = strrpos($relevantText, $keyword);
                if ($pos !== false) {
                    // Distance = jarak dari akhir keyword ke akhir text
                    $distance = strlen($relevantText) - ($pos + strlen($keyword));
                    if ($distance < $nearestDistance) {
                        $nearestDistance = $distance;
                        $nearestKeyword = $keyword;
                        $nearestType = $type;
                    }
                }
            }
        };

        // Cari keyword terdekat dari semua kategori
        // Prioritas: monthly/weekly > target (karena lebih spesifik)
        $findLastKeyword($monthlyKeywords, 'monthly');
        $findLastKeyword($weeklyKeywords, 'weekly');
        $findLastKeyword($targetKeywords, 'target');

        // Hanya return jika keyword cukup dekat (dalam 50 karakter)
        if ($nearestDistance <= 50 && $nearestType !== null) {
            return $nearestType;
        }

        return null;
    }
}
