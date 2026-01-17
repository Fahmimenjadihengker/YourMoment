<?php

namespace App\Services;

/**
 * ChatIntentDetector
 * 
 * Mendeteksi MULTI-INTENT dari pesan chat user.
 * 
 * Mendukung intent:
 * - 'goal_simulation' → simulasi target finansial
 * - 'future_budget_planning' → rencana budget masa depan
 * - 'recommendation' → saran/rekomendasi/tips
 * - 'report_saldo' → data saldo/balance
 * - 'report_pengeluaran' → data pengeluaran/spending
 * - 'report_pemasukan' → data pemasukan/income
 * - 'report_kategori' → breakdown kategori
 * - 'greeting' → sapaan
 * - 'help' → bantuan
 * 
 * Return: array of intents (bisa lebih dari 1)
 * Default fallback: ['recommendation']
 */
class ChatIntentDetector
{
    /**
     * Pattern untuk mendeteksi GOAL_SIMULATION (PRIORITY 1)
     * User bertanya tentang simulasi target/goal dengan nominal
     */
    protected array $goalSimulationPatterns = [
        // "ingin/mau/pengen ... berapa lama/bulan"
        '/(?:ingin|mau|pengen|pengin).+(?:berapa\s*(?:lama|bulan|minggu)|kapan)/i',
        // "target ... berapa lama"
        '/target.+(?:berapa\s*(?:lama|bulan|minggu)|kapan)/i',
        // "budget ... berapa lama"  
        '/budget.+(?:berapa\s*(?:lama|bulan|minggu)|kapan)/i',
        // "kira-kira berapa lama"
        '/kira[- ]?kira\s*berapa\s*(?:lama|bulan|minggu)/i',
        // "butuh berapa lama/bulan"
        '/butuh\s*berapa\s*(?:lama|bulan|minggu)/i',
        // "bisa tercapai dalam berapa"
        '/(?:bisa|dapat|bsa)\s*(?:tercapai|terkumpul|kesampean).+berapa/i',
        // "nabung ... sampai dapat/bisa"
        '/nabung.+(?:sampai|hingga|biar)\s*(?:dapat|bisa|dapet)/i',
        // "simulasi target/tabungan"
        '/simulasi\s*(?:target|tabungan|nabung|saving)/i',
        // "hitung ... berapa lama"
        '/hitung.+berapa\s*(?:lama|bulan)/i',
        // Explicit: "kalau nabung X, target Y berapa lama"
        '/(?:kalau|kalo|jika|bila)\s*nabung.+target.+berapa/i',
        '/(?:kalau|kalo|jika|bila)\s*sisih.+target.+berapa/i',
        // "beli X dengan uang jajan Y"
        '/(?:beli|rakit).+(?:dengan|uang\s*jajan).+(?:berapa|lama|bisa|kapan)/i',
        // Explicit goal questions
        '/(?:ingin|mau|pengen)\s*(?:beli|rakit|punya).+\d+\s*(?:jt|juta|rb|ribu)/i',
    ];

    /**
     * Kata kunci STRONG untuk GOAL_SIMULATION - jika ada ini + nominal = goal simulation
     */
    protected array $goalSimulationStrongKeywords = [
        'berapa lama',
        'berapa bulan',
        'berapa minggu',
        'kapan tercapai',
        'kapan bisa',
        'kapan terkumpul',
        'kira kira',
        'kira-kira',
        'butuh waktu',
        'estimasi waktu',
        'simulasi',
    ];

    /**
     * Kata kunci INTENT untuk GOAL_SIMULATION - menunjukkan keinginan beli/target
     */
    protected array $goalIntentKeywords = [
        'ingin beli',
        'mau beli',
        'pengen beli',
        'ingin rakit',
        'mau rakit',
        'pengen rakit',
        'ingin punya',
        'mau punya',
        'target beli',
        'target punya',
        'nabung untuk',
        'nabung buat',
    ];

    /**
     * Kata kunci untuk intent RECOMMENDATION
     * User ingin mendapat saran, rekomendasi, atau panduan
     */
    protected array $recommendationKeywords = [
        // Langsung minta saran
        'rekomendasi',
        'rekomendasikan',
        'sarankan',
        'saran',
        'advice',
        'suggest',
        'suggestion',
        'tips',

        // Pertanyaan cara/bagaimana
        'bagaimana',
        'gimana',
        'gmn',
        'cara',
        'how',

        // Pertanyaan apa yang harus dilakukan
        'apa yang harus',
        'apa yg harus',
        'sebaiknya',
        'seharusnya',
        'harusnya',
        'should',
        'mending',
        'mendingan',
        'better',

        // Minta panduan
        'bisa bantu',
        'tolong bantu',
        'help me',
        'kasih tau',
        'kasih tahu',
        'beritahu',
        'ajarin',
        'ajari',

        // Hemat/optimasi
        'hemat',
        'irit',
        'save',
        'saving',
        'efisien',
        'optimalkan',
        'optimize',

        // Evaluasi/analisis untuk saran
        'evaluasi',
        'analyze',
        'analisis',
        'review',

        // Pertanyaan opini
        'menurut',
        'pendapat',
        'opinion',
        'think',
        'pikir',

        // Strategi/planning
        'strategi',
        'strategy',
        'rencana',
        'plan',
        'planning',

        // Problem solving
        'solusi',
        'solution',
        'atasi',
        'kurangi',
        'reduce',
        'improve',
        'perbaiki',
    ];

    /**
     * Kata kunci untuk intent REPORT/DATA
     * User ingin mendapat informasi/data/statistik
     */
    protected array $reportKeywords = [
        // Pertanyaan kuantitas
        'berapa',
        'how much',
        'how many',
        'jumlah',

        // Data/statistik
        'total',
        'statistik',
        'statistic',
        'data',

        // Laporan
        'laporan',
        'report',
        'summary',
        'rangkuman',
        'ringkasan',

        // Info spesifik
        'saldo',
        'balance',
        'sisanya',
        'sisa',
        'remaining',

        // Breakdown
        'breakdown',
        'rincian',
        'detail',
        'rinci',

        // History
        'history',
        'riwayat',
        'catatan',
        'record',

        // Cek/lihat
        'cek',
        'check',
        'lihat',
        'tampilkan',
        'show',
        'display',

        // Pengeluaran/pemasukan sebagai data
        'pengeluaran saya',
        'pemasukan saya',
        'spending saya',
        'income saya',
        'expense saya',
    ];

    /**
     * Pattern kombinasi yang menunjukkan RECOMMENDATION
     * Meskipun ada kata kunci report, kombinasi ini = recommendation
     */
    protected array $recommendationPatterns = [
        '/rekomendasi.*(pengeluaran|makan|transport|nongkrong)/i',
        '/saran.*(pengeluaran|hemat|keuangan|budget)/i',
        '/bagaimana.*(hemat|atur|manage|kurangi)/i',
        '/gimana.*(hemat|atur|manage|kurangi)/i',
        '/cara.*(hemat|atur|manage|nabung|saving)/i',
        '/tips.*(hemat|keuangan|nabung|budget)/i',
        '/apa yang harus.*(lakukan|dilakukan|saya)/i',
        '/sebaiknya.*(apa|gimana|bagaimana)/i',
        '/bisa.*(hemat|kurangi|atur)/i',
    ];

    /**
     * Pattern kombinasi yang menunjukkan REPORT
     */
    protected array $reportPatterns = [
        '/berapa.*(total|saldo|pengeluaran|pemasukan|sisanya)/i',
        '/total.*(pengeluaran|pemasukan|expense|income)/i',
        '/cek.*(saldo|balance|pengeluaran)/i',
        '/lihat.*(data|statistik|laporan|riwayat)/i',
        '/tampilkan.*(data|statistik|laporan|breakdown)/i',
    ];

    // =====================================================
    // MULTI-INTENT KEYWORDS
    // =====================================================

    /**
     * Kata kunci untuk REPORT_SALDO
     */
    protected array $saldoKeywords = [
        'saldo',
        'balance',
        'uang saya',
        'uang ku',
        'uangku',
        'duit saya',
        'duit ku',
        'duitku',
        'dompet',
        'wallet',
        'sisa uang',
        'berapa uang',
    ];

    /**
     * Kata kunci untuk REPORT_PENGELUARAN
     */
    protected array $pengeluaranKeywords = [
        'pengeluaran',
        'expense',
        'expenses',
        'spending',
        'spend',
        'habis',
        'keluar',
        'uang keluar',
        'total keluar',
        'boros',
    ];

    /**
     * Mapping kategori RESMI untuk filter pengeluaran
     * HANYA nama kategori dan alias langsung, BUKAN synonym
     * 
     * Key: keyword yang user ketik (lowercase)
     * Value: nama kategori di database
     * 
     * IMPORTANT: Kata seperti 'bensin', 'ojek', 'gojek' TIDAK termasuk di sini
     * karena itu akan di-handle sebagai searchKeyword untuk filter description
     */
    protected array $officialCategoryKeywords = [
        // Makan - hanya nama kategori dan alias langsung
        'makan' => 'Makan',
        'makanan' => 'Makan',
        'food' => 'Makan',

        // Transport - hanya nama kategori dan alias langsung
        'transport' => 'Transport',
        'transportasi' => 'Transport',
        'transportation' => 'Transport',

        // Nongkrong - hanya nama kategori dan alias langsung
        'nongkrong' => 'Nongkrong',
        'hangout' => 'Nongkrong',
        'hiburan' => 'Nongkrong',
        'entertainment' => 'Nongkrong',

        // Akademik - hanya nama kategori dan alias langsung
        'akademik' => 'Akademik',
        'pendidikan' => 'Akademik',
        'education' => 'Akademik',

        // Lainnya - hanya nama kategori dan alias langsung
        'lainnya' => 'Lainnya',
        'lain' => 'Lainnya',
        'other' => 'Lainnya',
        'others' => 'Lainnya',
    ];

    /**
     * Kata kunci untuk REPORT_PEMASUKAN
     */
    protected array $pemasukanKeywords = [
        'pemasukan',
        'income',
        'pendapatan',
        'uang masuk',
        'terima',
        'gajian',
        'gaji',
        'penghasilan',
    ];

    /**
     * Kata kunci untuk REPORT_KATEGORI
     */
    protected array $kategoriKeywords = [
        'kategori',
        'category',
        'breakdown',
        'rincian',
        'detail',
        'per kategori',
    ];

    /**
     * Kata kunci untuk GREETING
     */
    protected array $greetingKeywords = [
        'hai',
        'halo',
        'hi',
        'hello',
        'hey',
        'pagi',
        'siang',
        'sore',
        'malam',
        'selamat pagi',
        'selamat siang',
        'selamat sore',
        'selamat malam',
    ];

    /**
     * Kata kunci untuk HELP
     */
    protected array $helpKeywords = [
        'help',
        'bantu',
        'bantuan',
        'bisa apa',
        'fitur',
        'menu',
        'apa saja',
    ];

    /**
     * Kata kunci untuk FUTURE_BUDGET_PLANNING
     * User ingin membuat rencana budget masa depan
     */
    protected array $futureBudgetPlanningKeywords = [
        // Waktu masa depan
        'minggu depan',
        'bulan depan',
        'ke depan',
        'kedepan',
        'besok',
        'nanti',
        'akan datang',

        // Planning keywords
        'rencana',
        'rencanakan',
        'planning',
        'plan',
        'alokasi',
        'alokasikan',
        'atur budget',
        'atur anggaran',
        'budget untuk',
        'anggaran untuk',

        // Specific future planning
        'buatkan rencana',
        'bikin rencana',
        'buat planning',
        'susun budget',
        'susun anggaran',
    ];

    /**
     * Pattern untuk detect FUTURE_BUDGET_PLANNING
     */
    protected array $futureBudgetPlanningPatterns = [
        // "buatkan rekomendasi X selama Y ke depan"
        '/(?:buatkan|buat|bikin|kasih)\s+(?:rekomendasi|rencana|planning|budget|anggaran).+(?:ke\s*depan|depan|nanti|akan\s*datang)/i',
        // "rencana X untuk Y depan"
        '/(?:rencana|planning|budget|anggaran).+(?:minggu|bulan|hari)\s*(?:depan|ke\s*depan)/i',
        // "alokasi budget X minggu/bulan depan"
        '/(?:alokasi|atur).+(?:budget|anggaran|uang).+(?:depan|ke\s*depan)/i',
        // "budget X untuk minggu/bulan depan"
        '/(?:budget|anggaran)\s+(?:\w+\s+)?(?:untuk|selama)\s+(?:\d+\s+)?(?:minggu|bulan|hari)\s*(?:depan|ke\s*depan)/i',
        // "berapa budget X minggu depan"
        '/berapa.+(?:budget|anggaran|alokasi).+(?:depan|ke\s*depan)/i',
        // "dengan uang yang saya miliki X depan"
        '/(?:dengan|pakai|menggunakan)\s+(?:uang|saldo|duit).+(?:yang\s+(?:saya|aku|ku)\s+(?:miliki|punya)|sekarang|saat\s*ini).+(?:depan|ke\s*depan)/i',
        // "cara hemat dengan saldo X depan"
        '/(?:cara|gimana|bagaimana)\s+(?:paling\s+)?(?:hemat|irit).+(?:depan|ke\s*depan)/i',
    ];

    /**
     * Keywords untuk detect BALANCE-BASED budget planning
     * Jika user menyebut "uang yang saya miliki" / "saldo saat ini" = gunakan saldo sebagai basis
     */
    protected array $balanceBasedKeywords = [
        'uang yang saya miliki',
        'uang yang aku miliki',
        'uang yang ku miliki',
        'uang yang aku punya',
        'uang yang saya punya',
        'duit yang saya miliki',
        'duit yang aku miliki',
        'duit yang ku miliki',
        'saldo saya',
        'saldo aku',
        'saldo ku',
        'saldo saat ini',
        'saldo sekarang',
        'uang saya sekarang',
        'uang aku sekarang',
        'jumlah uang saya',
        'jumlah uang aku',
        'dengan saldo',
        'pakai saldo',
        'menggunakan saldo',
        'uang yang ada',
        'duit yang ada',
        'modal yang ada',
        'budget yang ada',
        'paling hemat',
        'cara hemat',
        'cara paling hemat',
        'strategi hemat',
    ];

    /**
     * Detect MULTIPLE intents dari pesan user
     * 
     * Return array of intents berdasarkan kata kunci yang ditemukan.
     * Urutan intent mengikuti urutan kemunculan dalam pesan.
     * 
     * Intent bisa berupa string atau array dengan metadata:
     * - String: 'report_saldo', 'greeting', etc
     * - Array: ['type' => 'report_pengeluaran', 'category' => 'Makan']
     * 
     * @param string $message Pesan dari user
     * @return array Array of intents
     */
    public function detectMultiple(string $message): array
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $intents = [];
        $intentPositions = []; // Track position untuk sorting
        $intentData = []; // Store additional data per intent

        // PRIORITY 1: Cek GOAL_SIMULATION dulu (prioritas tertinggi)
        if ($this->isGoalSimulation($normalizedMessage)) {
            return ['goal_simulation'];
        }

        // PRIORITY 2: Cek FUTURE_BUDGET_PLANNING (rencana budget masa depan)
        if ($this->isFutureBudgetPlanning($normalizedMessage)) {
            $category = $this->detectCategoryFilter($normalizedMessage);
            $period = $this->detectFuturePeriod($normalizedMessage);
            $periodCount = $this->extractPeriodCount($normalizedMessage, $period);
            $useBalance = $this->isBalanceBasedPlanning($normalizedMessage);
            return [[
                'type' => 'future_budget_planning',
                'category' => $category,
                'period' => $period,
                'periodCount' => $periodCount,
                'useBalance' => $useBalance,
            ]];
        }

        // Cek GREETING (jika ini saja, return langsung)
        if ($this->isOnlyGreeting($normalizedMessage)) {
            return ['greeting'];
        }

        // Cek HELP (jika ini saja, return langsung)
        if ($this->isOnlyHelp($normalizedMessage)) {
            return ['help'];
        }

        // Cek REPORT_SALDO
        $saldoPos = $this->findKeywordPosition($normalizedMessage, $this->saldoKeywords);
        if ($saldoPos !== false) {
            $intentPositions['report_saldo'] = $saldoPos;
        }

        // Cek REPORT_PENGELUARAN + deteksi kategori atau searchKeyword
        $pengeluaranPos = $this->findKeywordPosition($normalizedMessage, $this->pengeluaranKeywords);
        if ($pengeluaranPos !== false) {
            $intentPositions['report_pengeluaran'] = $pengeluaranPos;

            // PRIORITY 1: Detect official category filter
            $category = $this->detectCategoryFilter($normalizedMessage);
            if ($category !== null) {
                $intentData['report_pengeluaran'] = ['category' => $category];
            } else {
                // PRIORITY 2: Extract searchKeyword untuk filter by description
                $searchKeyword = $this->extractSearchKeyword($normalizedMessage);
                if ($searchKeyword !== null) {
                    $intentData['report_pengeluaran'] = ['searchKeyword' => $searchKeyword];
                }
            }
        }

        // Cek REPORT_PEMASUKAN
        $pemasukanPos = $this->findKeywordPosition($normalizedMessage, $this->pemasukanKeywords);
        if ($pemasukanPos !== false) {
            $intentPositions['report_pemasukan'] = $pemasukanPos;
        }

        // Cek REPORT_KATEGORI
        $kategoriPos = $this->findKeywordPosition($normalizedMessage, $this->kategoriKeywords);
        if ($kategoriPos !== false) {
            $intentPositions['report_kategori'] = $kategoriPos;
        }

        // Cek RECOMMENDATION
        $recPos = $this->findKeywordPosition($normalizedMessage, $this->recommendationKeywords);
        if ($recPos !== false || $this->matchesPatterns($normalizedMessage, $this->recommendationPatterns)) {
            $intentPositions['recommendation'] = $recPos !== false ? $recPos : 0;
        }

        // Sort berdasarkan posisi kemunculan dalam pesan
        if (!empty($intentPositions)) {
            asort($intentPositions);

            // Build final intents array with metadata
            foreach (array_keys($intentPositions) as $intentKey) {
                if (isset($intentData[$intentKey])) {
                    // Intent with metadata
                    $intents[] = array_merge(['type' => $intentKey], $intentData[$intentKey]);
                } else {
                    // Simple string intent
                    $intents[] = $intentKey;
                }
            }
        }

        // Fallback: jika tidak ada intent terdeteksi, default recommendation
        if (empty($intents)) {
            $intents = ['recommendation'];
        }

        return $intents;
    }

    /**
     * Detect category filter dari pesan user
     * 
     * HANYA match jika user menyebut nama kategori resmi atau alias langsung:
     * - Makan, makanan, food
     * - Transport, transportasi
     * - Nongkrong, hangout, hiburan
     * - Akademik, pendidikan
     * - Lainnya, lain, other
     * 
     * Kata seperti 'bensin', 'ojek', 'gojek' TIDAK akan match di sini
     * dan akan di-handle oleh extractSearchKeyword() untuk filter description
     * 
     * @param string $message Normalized message
     * @return string|null Nama kategori atau null jika tidak ada
     */
    public function detectCategoryFilter(string $message): ?string
    {
        foreach ($this->officialCategoryKeywords as $keyword => $categoryName) {
            // Word boundary match untuk menghindari false positive
            // Contoh: "makan" tidak match "dimakan"
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            if (preg_match($pattern, $message)) {
                return $categoryName;
            }
        }

        return null;
    }

    /**
     * Extract search keyword dari pesan untuk filter by description
     * 
     * Mengambil kata setelah "pengeluaran" yang bukan kata umum/noise
     * Contoh: "pengeluaran topup" → "topup"
     *         "pengeluaran genshin" → "genshin"
     *         "pengeluaran steam bulan ini" → "steam"
     * 
     * @param string $message Normalized message
     * @return string|null Search keyword atau null
     */
    public function extractSearchKeyword(string $message): ?string
    {
        // Pattern: ambil kata setelah kata kunci pengeluaran
        $pengeluaranKeywordsPattern = implode('|', array_map(function ($k) {
            return preg_quote($k, '/');
        }, $this->pengeluaranKeywords));

        // Match: pengeluaran/expense/spending + [kata berikutnya]
        $pattern = '/(?:' . $pengeluaranKeywordsPattern . ')\s+([a-zA-Z0-9_\-]+)/i';

        if (preg_match($pattern, $message, $matches)) {
            $keyword = strtolower(trim($matches[1]));

            // Filter out noise words (kata umum yang bukan search term)
            $noiseWords = [
                'saya',
                'ku',
                'aku',
                'gue',
                'gw',
                'kamu',
                'anda',
                'bulan',
                'minggu',
                'hari',
                'tahun',
                'ini',
                'itu',
                'kemarin',
                'lalu',
                'terakhir',
                'total',
                'semua',
                'seluruh',
                'berapa',
                'untuk',
                'buat',
                'dari',
                'ke',
                'di',
                'yang',
                'adalah',
                'ada',
                'nya',
                'dan',
                'atau',
            ];

            // Jika keyword adalah noise word, return null
            if (in_array($keyword, $noiseWords)) {
                return null;
            }

            // Jika keyword adalah nama kategori RESMI, skip (sudah di-handle detectCategoryFilter)
            // Kata seperti 'bensin', 'ojek', 'gojek' TIDAK di-skip karena bukan kategori resmi
            if (array_key_exists($keyword, $this->officialCategoryKeywords)) {
                return null;
            }

            // Minimal 2 karakter
            if (strlen($keyword) < 2) {
                return null;
            }

            return $keyword;
        }

        return null;
    }

    /**
     * Get all available category names
     * 
     * @return array List of unique category names
     */
    public function getAvailableCategories(): array
    {
        return array_unique(array_values($this->officialCategoryKeywords));
    }

    /**
     * Detect single intent dari pesan user (backward compatible)
     * 
     * PRIORITY ORDER:
     * 1. Goal Simulation (highest) - jika ada target + allowance + time question
     * 2. Recommendation - jika minta saran/tips
     * 3. Report - jika minta data/statistik
     * 
     * @param string $message Pesan dari user
     * @return string 'goal_simulation', 'recommendation', atau 'report'
     * @deprecated Use detectMultiple() for multi-intent support
     */
    public function detect(string $message): string
    {
        $intents = $this->detectMultiple($message);

        // Return first intent (for backward compatibility)
        return $intents[0] ?? 'recommendation';
    }

    /**
     * Find earliest position of any keyword in message
     * 
     * @return int|false Position or false if not found
     */
    protected function findKeywordPosition(string $message, array $keywords): int|false
    {
        $earliestPos = false;

        foreach ($keywords as $keyword) {
            $pos = strpos($message, $keyword);
            if ($pos !== false) {
                if ($earliestPos === false || $pos < $earliestPos) {
                    $earliestPos = $pos;
                }
            }
        }

        return $earliestPos;
    }

    /**
     * Check if message is ONLY a greeting (no other intent)
     */
    protected function isOnlyGreeting(string $message): bool
    {
        // Check if starts with greeting
        foreach ($this->greetingKeywords as $greeting) {
            if (strpos($message, $greeting) === 0) {
                // Check if message is short (just greeting)
                if (strlen($message) < 30) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if message is ONLY asking for help
     */
    protected function isOnlyHelp(string $message): bool
    {
        foreach ($this->helpKeywords as $help) {
            if (strpos($message, $help) !== false) {
                // Check if message is short
                if (strlen($message) < 30) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check apakah message adalah future budget planning request
     * 
     * Criteria:
     * 1. Mengandung kata kunci waktu masa depan (minggu depan, bulan depan, ke depan)
     * 2. ATAU match pattern planning
     * 3. BUKAN goal simulation (tidak ada pertanyaan "berapa lama")
     */
    protected function isFutureBudgetPlanning(string $message): bool
    {
        // Jika ada pertanyaan "berapa lama/bulan" = goal simulation, bukan planning
        if (preg_match('/berapa\s*(?:lama|bulan|minggu)/', $message)) {
            return false;
        }

        // Check if matches future budget planning patterns
        if ($this->matchesPatterns($message, $this->futureBudgetPlanningPatterns)) {
            return true;
        }

        // Check keywords
        foreach ($this->futureBudgetPlanningKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect future period dari pesan user
     * 
     * @param string $message Normalized message
     * @return string 'minggu' atau 'bulan' (default: 'minggu')
     */
    public function detectFuturePeriod(string $message): string
    {
        // Check for bulan depan / 1 bulan ke depan
        if (preg_match('/(?:(\d+)\s*)?bulan\s*(?:depan|ke\s*depan)/i', $message, $matches)) {
            return 'bulan';
        }

        // Check for minggu depan / 1 minggu ke depan
        if (preg_match('/(?:(\d+)\s*)?minggu\s*(?:depan|ke\s*depan)/i', $message, $matches)) {
            return 'minggu';
        }

        // Check for hari depan
        if (preg_match('/(?:(\d+)\s*)?hari\s*(?:depan|ke\s*depan)/i', $message, $matches)) {
            return 'hari';
        }

        // Default to minggu
        return 'minggu';
    }

    /**
     * Extract jumlah periode dari pesan
     * Contoh: "2 minggu ke depan" -> 2
     * 
     * @param string $message Normalized message
     * @param string|null $period Period type (minggu/bulan/hari), unused but for consistency
     * @return int Jumlah periode (default: 1)
     */
    public function extractPeriodCount(string $message, ?string $period = null): int
    {
        // Match "X minggu/bulan/hari ke depan"
        if (preg_match('/(\d+)\s*(?:minggu|bulan|hari)\s*(?:depan|ke\s*depan)/i', $message, $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }

    /**
     * Check apakah user ingin budget planning berdasarkan SALDO saat ini
     * 
     * Contoh message yang trigger balance-based:
     * - "dengan jumlah uang yang saya miliki sekarang"
     * - "saldo saya untuk 1 bulan ke depan"
     * - "cara paling hemat 1 bulan ke depan"
     * 
     * @param string $message Normalized message
     * @return bool True jika user ingin pakai saldo sebagai basis
     */
    public function isBalanceBasedPlanning(string $message): bool
    {
        foreach ($this->balanceBasedKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check apakah message adalah goal simulation request
     * 
     * Criteria (salah satu):
     * 1. Match goal simulation pattern + ada nominal
     * 2. Ada strong time keyword + ada 2 nominal (target & allowance)
     * 3. Ada goal intent keyword + ada nominal + ada time keyword
     */
    protected function isGoalSimulation(string $message): bool
    {
        $hasMoneyAmount = $this->hasMoneyAmount($message);

        // Tidak ada nominal = bukan goal simulation
        if (!$hasMoneyAmount) {
            return false;
        }

        // Criteria 1: Match explicit goal simulation patterns
        if ($this->matchesPatterns($message, $this->goalSimulationPatterns)) {
            return true;
        }

        // Criteria 2: Ada strong time keyword + minimal 2 nominal
        $hasStrongTimeKeyword = false;
        foreach ($this->goalSimulationStrongKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $hasStrongTimeKeyword = true;
                break;
            }
        }

        if ($hasStrongTimeKeyword) {
            // Check if there are at least 2 money amounts (target + allowance)
            preg_match_all('/\d+\s*(?:jt|juta|rb|ribu|k)/i', $message, $matches);
            if (count($matches[0]) >= 2) {
                return true;
            }
            // Even 1 nominal with strong time keyword could be goal simulation
            return true;
        }

        // Criteria 3: Ada goal intent keyword + nominal + time question
        $hasGoalIntent = false;
        foreach ($this->goalIntentKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $hasGoalIntent = true;
                break;
            }
        }

        if ($hasGoalIntent && $hasMoneyAmount) {
            // Check for any time-related question
            if (preg_match('/berapa|kapan|lama|bulan|minggu/i', $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check apakah message mengandung nominal uang
     */
    protected function hasMoneyAmount(string $message): bool
    {
        // Pattern untuk mendeteksi nominal uang
        $moneyPatterns = [
            '/\d+\s*(?:jt|juta)/i',
            '/\d+\s*(?:rb|ribu|k)/i',
            '/\d{6,}/', // Angka 6 digit ke atas (100rb+)
            '/rp\s*\d+/i',
        ];

        foreach ($moneyPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract goal simulation data dari message
     * 
     * @return array ['target' => int|null, 'monthly' => int|null, 'weekly' => int|null]
     */
    public function extractGoalSimulationData(string $message): array
    {
        return \App\Services\GoalSimulationService::extractAmounts($message);
    }

    /**
     * Detect intent dengan detail scoring (untuk debugging)
     * 
     * @param string $message
     * @return array
     */
    public function detectWithDetails(string $message): array
    {
        $normalizedMessage = $this->normalizeMessage($message);

        $recommendationPatternMatch = $this->matchesPatterns($normalizedMessage, $this->recommendationPatterns);
        $reportPatternMatch = $this->matchesPatterns($normalizedMessage, $this->reportPatterns);

        $recommendationScore = $this->calculateScore($normalizedMessage, $this->recommendationKeywords);
        $reportScore = $this->calculateScore($normalizedMessage, $this->reportKeywords);

        $matchedRecommendationKeywords = $this->getMatchedKeywords($normalizedMessage, $this->recommendationKeywords);
        $matchedReportKeywords = $this->getMatchedKeywords($normalizedMessage, $this->reportKeywords);

        $intent = $this->detect($message);

        return [
            'intent' => $intent,
            'original_message' => $message,
            'normalized_message' => $normalizedMessage,
            'scores' => [
                'recommendation' => $recommendationScore,
                'report' => $reportScore,
            ],
            'pattern_matches' => [
                'recommendation' => $recommendationPatternMatch,
                'report' => $reportPatternMatch,
            ],
            'matched_keywords' => [
                'recommendation' => $matchedRecommendationKeywords,
                'report' => $matchedReportKeywords,
            ],
        ];
    }

    /**
     * Normalize message untuk processing
     */
    protected function normalizeMessage(string $message): string
    {
        // Lowercase
        $message = strtolower($message);

        // Remove extra whitespace
        $message = preg_replace('/\s+/', ' ', $message);

        // Trim
        $message = trim($message);

        return $message;
    }

    /**
     * Check apakah message cocok dengan salah satu pattern
     */
    protected function matchesPatterns(string $message, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hitung skor berdasarkan kata kunci yang ditemukan
     */
    protected function calculateScore(string $message, array $keywords): int
    {
        $score = 0;

        foreach ($keywords as $keyword) {
            // Gunakan word boundary untuk matching yang lebih akurat
            if (strpos($message, $keyword) !== false) {
                $score++;

                // Bonus jika keyword ada di awal kalimat
                if (strpos($message, $keyword) === 0) {
                    $score++;
                }
            }
        }

        return $score;
    }

    /**
     * Get list of matched keywords (untuk debugging)
     */
    protected function getMatchedKeywords(string $message, array $keywords): array
    {
        $matched = [];

        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $matched[] = $keyword;
            }
        }

        return $matched;
    }
}
