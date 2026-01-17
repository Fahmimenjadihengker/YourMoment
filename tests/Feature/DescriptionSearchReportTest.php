<?php

namespace Tests\Feature;

use App\Services\ChatIntentDetector;
use Tests\TestCase;

class DescriptionSearchReportTest extends TestCase
{
    protected ChatIntentDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ChatIntentDetector();
    }

    /**
     * Test extract searchKeyword for "pengeluaran topup"
     */
    public function test_extract_search_keyword_topup(): void
    {
        $message = 'pengeluaran topup bulan ini';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertEquals('topup', $keyword);
    }

    /**
     * Test extract searchKeyword for "pengeluaran genshin"
     */
    public function test_extract_search_keyword_genshin(): void
    {
        $message = 'pengeluaran genshin';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertEquals('genshin', $keyword);
    }

    /**
     * Test extract searchKeyword for "pengeluaran steam"
     */
    public function test_extract_search_keyword_steam(): void
    {
        $message = 'pengeluaran steam bulan ini';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertEquals('steam', $keyword);
    }

    /**
     * Test extract searchKeyword for "spending netflix"
     */
    public function test_extract_search_keyword_spending_netflix(): void
    {
        $message = 'spending netflix saya';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertEquals('netflix', $keyword);
    }

    /**
     * Test extract searchKeyword for "expense spotify"
     */
    public function test_extract_search_keyword_expense_spotify(): void
    {
        $message = 'expense spotify bulan ini';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertEquals('spotify', $keyword);
    }

    /**
     * Test no searchKeyword when noise word follows
     */
    public function test_no_search_keyword_for_noise_words(): void
    {
        $messages = [
            'pengeluaran saya bulan ini',
            'pengeluaran bulan ini',
            'pengeluaran untuk minggu ini',
            'pengeluaran aku kemarin',
        ];

        foreach ($messages as $message) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertNull($keyword, "Should be null for: {$message}");
        }
    }

    /**
     * Test no searchKeyword when OFFICIAL category keyword follows
     * Hanya nama kategori resmi (makan, transport, nongkrong, akademik, lainnya)
     */
    public function test_no_search_keyword_for_official_category_words(): void
    {
        $messages = [
            'pengeluaran makan bulan ini',
            'pengeluaran transport minggu ini',
            'pengeluaran nongkrong',
            'pengeluaran akademik',
            'pengeluaran lainnya',
        ];

        foreach ($messages as $message) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertNull($keyword, "Should be null for official category: {$message}");
        }
    }

    /**
     * TEST BUG FIX: Kata seperti bensin, ojek, gojek HARUS return searchKeyword
     * Karena mereka BUKAN kategori resmi
     */
    public function test_non_official_words_return_search_keyword(): void
    {
        $testCases = [
            'pengeluaran bensin bulan ini' => 'bensin',
            'pengeluaran ojek saya' => 'ojek',
            'pengeluaran gojek minggu ini' => 'gojek',
            'pengeluaran grab saya' => 'grab',
            'pengeluaran parkir bulan ini' => 'parkir',
            'pengeluaran jajan saya' => 'jajan',
            'pengeluaran kuliah semester ini' => 'kuliah',
            'pengeluaran buku saya' => 'buku',
            'pengeluaran ngafe saya' => 'ngafe',
            'pengeluaran cafe minggu ini' => 'cafe',
        ];

        foreach ($testCases as $message => $expected) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertEquals($expected, $keyword, "Should return '{$expected}' for: {$message}");
        }
    }

    /**
     * Test detectMultiple returns searchKeyword in metadata
     */
    public function test_detect_multiple_returns_search_keyword_metadata(): void
    {
        $message = 'pengeluaran topup bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertCount(1, $intents);
        $this->assertIsArray($intents[0]);
        $this->assertEquals('report_pengeluaran', $intents[0]['type']);
        $this->assertEquals('topup', $intents[0]['searchKeyword']);
        $this->assertArrayNotHasKey('category', $intents[0]);
    }

    /**
     * Test category takes priority over searchKeyword
     */
    public function test_category_takes_priority_over_search_keyword(): void
    {
        // "makan" is a category, should use category not searchKeyword
        $message = 'pengeluaran makan bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertIsArray($intents[0]);
        $this->assertEquals('report_pengeluaran', $intents[0]['type']);
        $this->assertEquals('Makan', $intents[0]['category']);
        $this->assertArrayNotHasKey('searchKeyword', $intents[0]);
    }

    /**
     * Test various game/service keywords
     */
    public function test_various_service_keywords(): void
    {
        $testCases = [
            'pengeluaran gopay' => 'gopay',
            'pengeluaran ovo' => 'ovo',
            'pengeluaran dana' => 'dana',
            'pengeluaran shopeepay' => 'shopeepay',
            'pengeluaran valorant' => 'valorant',
            'pengeluaran pubg' => 'pubg',
            'pengeluaran mlbb' => 'mlbb',
            'spending youtube' => 'youtube',
        ];

        foreach ($testCases as $message => $expected) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertEquals($expected, $keyword, "Failed for: {$message}");
        }
    }

    /**
     * Test case insensitive matching
     */
    public function test_case_insensitive_extraction(): void
    {
        $messages = [
            'pengeluaran TOPUP bulan ini',
            'PENGELUARAN topup bulan ini',
            'Pengeluaran TopUp bulan ini',
        ];

        foreach ($messages as $message) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertEquals('topup', $keyword, "Failed for: {$message}");
        }
    }

    /**
     * Test no searchKeyword for short keywords (< 2 chars)
     */
    public function test_no_search_keyword_for_short_words(): void
    {
        $message = 'pengeluaran a bulan ini';
        $keyword = $this->detector->extractSearchKeyword($message);

        $this->assertNull($keyword);
    }

    /**
     * Test alphanumeric keywords work
     */
    public function test_alphanumeric_keywords(): void
    {
        $testCases = [
            'pengeluaran ps5' => 'ps5',
            'pengeluaran steam2024' => 'steam2024',
            'pengeluaran m3' => 'm3',
        ];

        foreach ($testCases as $message => $expected) {
            $keyword = $this->detector->extractSearchKeyword($message);
            $this->assertEquals($expected, $keyword, "Failed for: {$message}");
        }
    }

    /**
     * Test global report when no category and no searchKeyword
     */
    public function test_global_report_when_no_filter(): void
    {
        $message = 'pengeluaran bulan ini berapa';
        $intents = $this->detector->detectMultiple($message);

        // Should return simple string intent without metadata
        $this->assertCount(1, $intents);

        if (is_array($intents[0])) {
            $this->assertEquals('report_pengeluaran', $intents[0]['type']);
            $this->assertArrayNotHasKey('category', $intents[0]);
            $this->assertArrayNotHasKey('searchKeyword', $intents[0]);
        } else {
            $this->assertEquals('report_pengeluaran', $intents[0]);
        }
    }
}
