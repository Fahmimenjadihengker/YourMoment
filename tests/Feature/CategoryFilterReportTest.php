<?php

namespace Tests\Feature;

use App\Services\ChatIntentDetector;
use Tests\TestCase;

class CategoryFilterReportTest extends TestCase
{
    protected ChatIntentDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ChatIntentDetector();
    }

    /**
     * Test detect category filter untuk Makan
     * HANYA nama kategori resmi dan alias langsung
     */
    public function test_detect_category_makan(): void
    {
        $messages = [
            'pengeluaran makan bulan ini',
            'pengeluaran makanan minggu ini',
            'berapa total spending food saya',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals('Makan', $category, "Failed for message: {$message}");
        }
    }

    /**
     * Test detect category filter untuk Transport
     * HANYA nama kategori resmi dan alias langsung
     */
    public function test_detect_category_transport(): void
    {
        $messages = [
            'pengeluaran transport bulan ini',
            'pengeluaran transportasi minggu ini',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals('Transport', $category, "Failed for message: {$message}");
        }
    }

    /**
     * Test detect category filter untuk Nongkrong
     * HANYA nama kategori resmi dan alias langsung
     */
    public function test_detect_category_nongkrong(): void
    {
        $messages = [
            'pengeluaran nongkrong bulan ini',
            'pengeluaran hiburan minggu ini',
            'total hangout saya',
            'pengeluaran entertainment saya',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals('Nongkrong', $category, "Failed for message: {$message}");
        }
    }

    /**
     * Test detect category filter untuk Akademik
     * HANYA nama kategori resmi dan alias langsung
     */
    public function test_detect_category_akademik(): void
    {
        $messages = [
            'pengeluaran akademik bulan ini',
            'total biaya pendidikan',
            'pengeluaran education saya',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals('Akademik', $category, "Failed for message: {$message}");
        }
    }

    /**
     * Test detect category filter untuk Lainnya
     */
    public function test_detect_category_lainnya(): void
    {
        $messages = [
            'pengeluaran lainnya bulan ini',
            'pengeluaran lain minggu ini',
            'spending other saya',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals('Lainnya', $category, "Failed for message: {$message}");
        }
    }

    /**
     * TEST BUG FIX: Kata-kata seperti bensin, ojek, gojek BUKAN kategori
     * Harus return null agar diproses sebagai searchKeyword
     */
    public function test_non_official_keywords_return_null(): void
    {
        $messages = [
            'pengeluaran bensin bulan ini',    // bensin bukan kategori resmi
            'pengeluaran ojek saya',           // ojek bukan kategori resmi
            'pengeluaran gojek minggu ini',    // gojek bukan kategori resmi
            'pengeluaran grab saya',           // grab bukan kategori resmi
            'pengeluaran parkir bulan ini',    // parkir bukan kategori resmi
            'pengeluaran jajan saya',          // jajan bukan kategori resmi
            'pengeluaran kuliah semester ini', // kuliah bukan kategori resmi
            'pengeluaran buku saya',           // buku bukan kategori resmi
            'pengeluaran ukt saya',            // ukt bukan kategori resmi
            'spending ngafe saya',             // ngafe bukan kategori resmi
            'pengeluaran cafe saya',           // cafe bukan kategori resmi
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertNull($category, "Should be null (not category) for: {$message}");
        }
    }

    /**
     * Test no category filter when no category mentioned
     */
    public function test_no_category_filter_when_not_mentioned(): void
    {
        $messages = [
            'pengeluaran bulan ini',
            'total pengeluaran saya',
            'spending minggu ini',
            'berapa pengeluaran saya',
        ];

        foreach ($messages as $message) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertNull($category, "Should be null for message: {$message}");
        }
    }

    /**
     * Test detectMultiple returns intent with category metadata
     */
    public function test_detect_multiple_returns_category_metadata(): void
    {
        $message = 'pengeluaran makan bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertCount(1, $intents);
        $this->assertIsArray($intents[0]);
        $this->assertEquals('report_pengeluaran', $intents[0]['type']);
        $this->assertEquals('Makan', $intents[0]['category']);
    }

    /**
     * TEST BUG FIX: "pengeluaran bensin" harus return searchKeyword, bukan category
     */
    public function test_bensin_returns_search_keyword_not_category(): void
    {
        $message = 'pengeluaran bensin bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertCount(1, $intents);
        $this->assertIsArray($intents[0]);
        $this->assertEquals('report_pengeluaran', $intents[0]['type']);

        // Harus searchKeyword, BUKAN category
        $this->assertArrayNotHasKey('category', $intents[0], 'Should NOT have category');
        $this->assertArrayHasKey('searchKeyword', $intents[0], 'Should have searchKeyword');
        $this->assertEquals('bensin', $intents[0]['searchKeyword']);
    }

    /**
     * Test detectMultiple returns simple string when no category
     */
    public function test_detect_multiple_returns_string_when_no_category(): void
    {
        $message = 'pengeluaran bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertCount(1, $intents);

        // Could be string or array without category
        if (is_array($intents[0])) {
            $this->assertEquals('report_pengeluaran', $intents[0]['type']);
            $this->assertArrayNotHasKey('category', $intents[0]);
        } else {
            $this->assertEquals('report_pengeluaran', $intents[0]);
        }
    }

    /**
     * Test multiple intents with category filter
     */
    public function test_multiple_intents_with_category(): void
    {
        $message = 'saldo saya dan pengeluaran makan bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertCount(2, $intents);

        // Check that one of them is pengeluaran with Makan category
        $pengeluaranIntent = null;
        foreach ($intents as $intent) {
            if (is_array($intent) && ($intent['type'] ?? '') === 'report_pengeluaran') {
                $pengeluaranIntent = $intent;
                break;
            }
        }

        $this->assertNotNull($pengeluaranIntent);
        $this->assertEquals('Makan', $pengeluaranIntent['category']);
    }

    /**
     * Test case insensitive category matching
     */
    public function test_case_insensitive_category_matching(): void
    {
        $messages = [
            'pengeluaran MAKAN bulan ini' => 'Makan',
            'pengeluaran Transport bulan ini' => 'Transport',
            'pengeluaran AKADEMIK bulan ini' => 'Akademik',
        ];

        foreach ($messages as $message => $expectedCategory) {
            $category = $this->detector->detectCategoryFilter($message);
            $this->assertEquals($expectedCategory, $category, "Failed for message: {$message}");
        }
    }

    /**
     * Test getAvailableCategories returns all categories
     */
    public function test_get_available_categories(): void
    {
        $categories = $this->detector->getAvailableCategories();

        $this->assertContains('Makan', $categories);
        $this->assertContains('Transport', $categories);
        $this->assertContains('Nongkrong', $categories);
        $this->assertContains('Akademik', $categories);
        $this->assertContains('Lainnya', $categories);
    }

    /**
     * Test word boundary - "makan" should not match "dimakan"
     */
    public function test_word_boundary_matching(): void
    {
        // "dimakan" seharusnya tidak match "makan"
        $message = 'uang saya dimakan inflasi';
        $category = $this->detector->detectCategoryFilter($message);

        // Seharusnya null karena tidak ada kategori yang match
        $this->assertNull($category, 'Should not match "makan" from "dimakan"');
    }
}
