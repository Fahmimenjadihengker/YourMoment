<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ChatIntentDetector;
use App\Services\FutureBudgetPlanningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FutureBudgetPlanningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ChatIntentDetector $detector;
    protected FutureBudgetPlanningService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->detector = new ChatIntentDetector();
        $this->service = app(FutureBudgetPlanningService::class);

        // Create expense categories
        $this->createCategories();
    }

    protected function createCategories(): void
    {
        $categories = [
            ['name' => 'Makan', 'type' => 'expense', 'icon' => '🍔'],
            ['name' => 'Transport', 'type' => 'expense', 'icon' => '🚗'],
            ['name' => 'Nongkrong', 'type' => 'expense', 'icon' => '☕'],
            ['name' => 'Akademik', 'type' => 'expense', 'icon' => '📚'],
            ['name' => 'Laundry', 'type' => 'expense', 'icon' => '👕'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }

    /**
     * ========================================
     * INTENT DETECTION TESTS
     * ========================================
     */

    /** @test */
    public function it_detects_future_budget_planning_with_ke_depan(): void
    {
        $messages = [
            'buatkan rekomendasi makanan selama 1 minggu ke depan',
            'rencana pengeluaran 2 minggu ke depan',
            'budget makan ke depan',
            'alokasi uang minggu depan',
        ];

        foreach ($messages as $message) {
            $intents = $this->detector->detectMultiple($message);
            $this->assertNotEmpty($intents, "No intents detected for: {$message}");

            $firstIntent = $intents[0];
            $intentType = is_array($firstIntent) ? $firstIntent['type'] : $firstIntent;
            $this->assertEquals('future_budget_planning', $intentType, "Expected future_budget_planning for: {$message}");
        }
    }

    /** @test */
    public function it_does_not_detect_future_planning_for_goal_simulation(): void
    {
        // "berapa lama" should be goal_simulation, not future_budget_planning
        $messages = [
            'berapa lama nabung 5jt',
            'berapa bulan untuk mencapai target 1jt',
        ];

        foreach ($messages as $message) {
            $intents = $this->detector->detectMultiple($message);
            $this->assertNotEmpty($intents, "No intents detected for: {$message}");
            $this->assertEquals('goal_simulation', $intents[0], "Expected goal_simulation for: {$message}");
        }
    }

    /** @test */
    public function it_detects_period_correctly(): void
    {
        // Weekly
        $this->assertEquals('minggu', $this->detector->detectFuturePeriod('rencana minggu depan'));
        $this->assertEquals('minggu', $this->detector->detectFuturePeriod('budget 2 minggu ke depan'));

        // Monthly
        $this->assertEquals('bulan', $this->detector->detectFuturePeriod('rencana bulan depan'));
        $this->assertEquals('bulan', $this->detector->detectFuturePeriod('alokasi 3 bulan ke depan'));

        // Daily
        $this->assertEquals('hari', $this->detector->detectFuturePeriod('budget 5 hari ke depan'));

        // Default should be minggu
        $this->assertEquals('minggu', $this->detector->detectFuturePeriod('rencana ke depan'));
    }

    /** @test */
    public function it_extracts_period_count_correctly(): void
    {
        // Numeric periods
        $this->assertEquals(2, $this->detector->extractPeriodCount('budget 2 minggu ke depan', 'minggu'));
        $this->assertEquals(3, $this->detector->extractPeriodCount('rencana 3 bulan ke depan', 'bulan'));
        $this->assertEquals(5, $this->detector->extractPeriodCount('alokasi 5 hari ke depan', 'hari'));

        // Default should be 1
        $this->assertEquals(1, $this->detector->extractPeriodCount('rencana minggu depan', 'minggu'));
        $this->assertEquals(1, $this->detector->extractPeriodCount('budget bulan depan', 'bulan'));
    }

    /** @test */
    public function it_detects_category_in_future_planning(): void
    {
        $intents = $this->detector->detectMultiple('buatkan rekomendasi makanan 1 minggu ke depan');

        $this->assertNotEmpty($intents);
        $firstIntent = $intents[0];
        $this->assertIsArray($firstIntent);
        $this->assertEquals('future_budget_planning', $firstIntent['type']);
        $this->assertEquals('Makan', $firstIntent['category']);
    }

    /** @test */
    public function it_detects_balance_based_planning(): void
    {
        $balanceBasedMessages = [
            'dengan jumlah uang yang saya miliki sekarang 1 bulan ke depan',
            'cara paling hemat 1 bulan ke depan',
            'dengan saldo saya untuk minggu depan',
            'strategi hemat bulan depan',
        ];

        foreach ($balanceBasedMessages as $message) {
            $intents = $this->detector->detectMultiple($message);
            $this->assertNotEmpty($intents, "No intents detected for: {$message}");

            $firstIntent = $intents[0];
            $this->assertIsArray($firstIntent, "Intent should be array for: {$message}");
            $this->assertEquals('future_budget_planning', $firstIntent['type'], "Expected future_budget_planning for: {$message}");
            $this->assertTrue($firstIntent['useBalance'], "Expected useBalance=true for: {$message}");
        }
    }

    /** @test */
    public function it_detects_non_balance_based_planning(): void
    {
        $normalMessages = [
            'rencana pengeluaran minggu depan',
            'budget makan bulan depan',
        ];

        foreach ($normalMessages as $message) {
            $intents = $this->detector->detectMultiple($message);
            $firstIntent = $intents[0];
            $this->assertIsArray($firstIntent);
            $this->assertEquals('future_budget_planning', $firstIntent['type']);
            $this->assertFalse($firstIntent['useBalance'], "Expected useBalance=false for: {$message}");
        }
    }

    /**
     * ========================================
     * SERVICE TESTS
     * ========================================
     */

    /** @test */
    public function it_generates_no_data_response_when_no_transactions(): void
    {
        $response = $this->service->generate($this->user->id, 'Makan', 'minggu', 1);

        $this->assertStringContainsString('Belum ada data pengeluaran', $response);
        $this->assertStringContainsString('Patokan Umum Budget Makan', $response);
    }

    /** @test */
    public function it_generates_balance_based_budget_for_monthly(): void
    {
        // Set user balance to 2.850.000
        \App\Models\WalletSetting::create([
            'user_id' => $this->user->id,
            'balance' => 2850000,
        ]);

        $response = $this->service->generate($this->user->id, null, 'bulan', 1, true);

        // Should contain balance info
        $this->assertStringContainsString('Saldo Kamu Saat Ini', $response);
        $this->assertStringContainsString('2.850.000', $response);

        // Should contain allocation info
        $this->assertStringContainsString('Alokasi Ideal per Kategori', $response);
        $this->assertStringContainsString('Makan', $response);
        $this->assertStringContainsString('40%', $response);

        // Should contain strategy
        $this->assertStringContainsString('Strategi Hemat', $response);
    }

    /** @test */
    public function it_calculates_correct_daily_budget_from_balance(): void
    {
        // Set user balance to 2.850.000
        \App\Models\WalletSetting::create([
            'user_id' => $this->user->id,
            'balance' => 3000000, // 3jt / 30 days = 100rb/hari
        ]);

        $response = $this->service->generate($this->user->id, null, 'bulan', 1, true);

        // Daily budget should be ~100.000
        $this->assertStringContainsString('100.000', $response);
    }

    /** @test */
    public function it_calculates_correct_category_allocation(): void
    {
        // Set balance to 1.000.000 for easy calculation
        // Makan 40% = 400.000
        \App\Models\WalletSetting::create([
            'user_id' => $this->user->id,
            'balance' => 1000000,
        ]);

        $response = $this->service->generate($this->user->id, null, 'bulan', 1, true);

        // Makan should be 400.000 (40%)
        $this->assertStringContainsString('400.000', $response);
    }

    /** @test */
    public function it_returns_no_balance_response_when_balance_is_zero(): void
    {
        // User has no wallet setting (balance = 0)
        $response = $this->service->generate($this->user->id, null, 'bulan', 1, true);

        $this->assertStringContainsString('Saldo kamu saat ini: Rp 0', $response);
        $this->assertStringContainsString('Update saldo', $response);
    }

    /** @test */
    public function it_generates_category_budget_with_transaction_data(): void
    {
        $category = Category::where('name', 'Makan')->first();

        // Create some transactions in the past weeks manually
        for ($i = 0; $i < 10; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 25000, // 25rb per transaction
                'transaction_date' => now()->subDays(rand(1, 21)),
                'description' => 'Test makan ' . ($i + 1),
            ]);
        }

        $response = $this->service->generate($this->user->id, 'Makan', 'minggu', 1);

        $this->assertStringContainsString('Rencana Budget Makan', $response);
        $this->assertStringContainsString('Analisis Pengeluaran Sebelumnya', $response);
        $this->assertStringContainsString('Rekomendasi Budget', $response);
        $this->assertStringContainsString('Per hari:', $response); // Weekly should have daily breakdown
        $this->assertStringContainsString('Tips:', $response);
    }

    /** @test */
    public function it_generates_global_budget_with_multiple_categories(): void
    {
        $makanCategory = Category::where('name', 'Makan')->first();
        $transportCategory = Category::where('name', 'Transport')->first();

        // Create transactions for multiple categories manually
        for ($i = 0; $i < 8; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $makanCategory->id,
                'type' => 'expense',
                'amount' => 30000,
                'transaction_date' => now()->subDays(rand(1, 21)),
                'description' => 'Test makan ' . ($i + 1),
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $transportCategory->id,
                'type' => 'expense',
                'amount' => 15000,
                'transaction_date' => now()->subDays(rand(1, 21)),
                'description' => 'Test transport ' . ($i + 1),
            ]);
        }

        // Generate global budget (no category specified)
        $response = $this->service->generate($this->user->id, null, 'minggu', 1);

        $this->assertStringContainsString('Rencana Budget', $response);
        $this->assertStringContainsString('Rekomendasi per Kategori', $response);
        $this->assertStringContainsString('Makan', $response);
        $this->assertStringContainsString('Transport', $response);
        $this->assertStringContainsString('Total Budget', $response);
    }

    /** @test */
    public function it_calculates_budget_with_10_percent_buffer(): void
    {
        $category = Category::where('name', 'Makan')->first();

        // Create 4 transactions, 100rb each = 400rb total over 4 weeks = 100rb/week average
        // With 10% buffer = 110rb recommended
        for ($i = 0; $i < 4; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 100000,
                'transaction_date' => now()->subWeeks($i),
                'description' => 'Test makan week ' . ($i + 1),
            ]);
        }

        $response = $this->service->generate($this->user->id, 'Makan', 'minggu', 1);

        // Should contain 110.000 (100.000 * 1.1 = 110.000)
        $this->assertStringContainsString('110.000', $response);
    }

    /** @test */
    public function it_handles_monthly_period(): void
    {
        $category = Category::where('name', 'Makan')->first();

        for ($i = 0; $i < 10; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 50000,
                'transaction_date' => now()->subDays(rand(1, 60)),
                'description' => 'Test makan ' . ($i + 1),
            ]);
        }

        $response = $this->service->generate($this->user->id, 'Makan', 'bulan', 1);

        $this->assertStringContainsString('Bulan Depan', $response);
        $this->assertStringContainsString('Per minggu:', $response);
        $this->assertStringContainsString('Per hari:', $response);
    }

    /** @test */
    public function it_handles_multiple_periods(): void
    {
        $category = Category::where('name', 'Transport')->first();

        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 20000,
                'transaction_date' => now()->subDays(rand(1, 21)),
                'description' => 'Test transport ' . ($i + 1),
            ]);
        }

        $response = $this->service->generate($this->user->id, 'Transport', 'minggu', 3);

        $this->assertStringContainsString('3 Minggu ke Depan', $response);
        $this->assertStringContainsString('Total 3 Minggu', $response);
    }

    /**
     * ========================================
     * INTEGRATION TESTS (Controller)
     * ========================================
     */

    /** @test */
    public function it_handles_chat_request_for_future_planning(): void
    {
        $this->actingAs($this->user);

        // Create some transaction data first
        $category = Category::where('name', 'Makan')->first();

        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 25000,
                'transaction_date' => now()->subDays(rand(1, 14)),
                'description' => 'Test makan ' . ($i + 1),
            ]);
        }

        $response = $this->postJson('/ai/chat', [
            'message' => 'buatkan rekomendasi makanan selama 1 minggu ke depan',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'response']);
        $this->assertStringContainsString('Budget', $response->json('response'));
    }
}
