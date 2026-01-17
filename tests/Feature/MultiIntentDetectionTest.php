<?php

namespace Tests\Feature;

use App\Services\ChatIntentDetector;
use Tests\TestCase;

class MultiIntentDetectionTest extends TestCase
{
    protected ChatIntentDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new ChatIntentDetector();
    }

    /**
     * Test multi-intent: saldo + pengeluaran
     */
    public function test_detect_saldo_dan_pengeluaran(): void
    {
        $message = 'saldo saya dan pengeluaran';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_saldo', $intents, 'Should detect report_saldo');
        $this->assertContains('report_pengeluaran', $intents, 'Should detect report_pengeluaran');
        $this->assertCount(2, $intents, 'Should have exactly 2 intents');
    }

    /**
     * Test multi-intent: pengeluaran + pemasukan
     */
    public function test_detect_pengeluaran_dan_pemasukan(): void
    {
        $message = 'lihat pengeluaran dan pemasukan bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_pengeluaran', $intents, 'Should detect report_pengeluaran');
        $this->assertContains('report_pemasukan', $intents, 'Should detect report_pemasukan');
    }

    /**
     * Test multi-intent: saldo + kategori
     */
    public function test_detect_saldo_dan_kategori(): void
    {
        $message = 'berapa saldo saya dan breakdown kategori';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_saldo', $intents, 'Should detect report_saldo');
        $this->assertContains('report_kategori', $intents, 'Should detect report_kategori');
    }

    /**
     * Test single intent: hanya saldo
     */
    public function test_detect_single_saldo(): void
    {
        $message = 'berapa saldo saya';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_saldo', $intents, 'Should detect report_saldo');
        $this->assertCount(1, $intents, 'Should have exactly 1 intent');
    }

    /**
     * Test single intent: hanya pengeluaran
     */
    public function test_detect_single_pengeluaran(): void
    {
        $message = 'total pengeluaran minggu ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_pengeluaran', $intents, 'Should detect report_pengeluaran');
    }

    /**
     * Test goal simulation priority (should return only goal_simulation)
     */
    public function test_goal_simulation_priority(): void
    {
        $message = 'ingin beli laptop 15jt dengan uang jajan 2jt sebulan, berapa lama';
        $intents = $this->detector->detectMultiple($message);

        $this->assertEquals(['goal_simulation'], $intents, 'Goal simulation should be exclusive');
    }

    /**
     * Test greeting only
     */
    public function test_greeting_only(): void
    {
        $message = 'hai';
        $intents = $this->detector->detectMultiple($message);

        $this->assertEquals(['greeting'], $intents, 'Should detect greeting only');
    }

    /**
     * Test help only
     */
    public function test_help_only(): void
    {
        $message = 'help';
        $intents = $this->detector->detectMultiple($message);

        $this->assertEquals(['help'], $intents, 'Should detect help only');
    }

    /**
     * Test recommendation intent
     */
    public function test_recommendation_intent(): void
    {
        $message = 'kasih saran hemat dong';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('recommendation', $intents, 'Should detect recommendation');
    }

    /**
     * Test urutan intent mengikuti urutan dalam pesan
     */
    public function test_intent_order_follows_message(): void
    {
        // Saldo dulu, lalu pengeluaran
        $message1 = 'saldo saya dan pengeluaran';
        $intents1 = $this->detector->detectMultiple($message1);

        // Pengeluaran dulu, lalu saldo
        $message2 = 'pengeluaran dan saldo saya';
        $intents2 = $this->detector->detectMultiple($message2);

        // Urutan harus berbeda
        $this->assertEquals('report_saldo', $intents1[0], 'First intent should be saldo');
        $this->assertEquals('report_pengeluaran', $intents2[0], 'First intent should be pengeluaran');
    }

    /**
     * Test fallback to recommendation
     */
    public function test_fallback_recommendation(): void
    {
        $message = 'apa kabar hari ini cuaca bagus';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('recommendation', $intents, 'Should fallback to recommendation');
    }

    /**
     * Test backward compatible detect() method
     */
    public function test_backward_compatible_detect(): void
    {
        $message = 'saldo saya dan pengeluaran';
        $intent = $this->detector->detect($message);

        // Should return first intent as string
        $this->assertIsString($intent, 'detect() should return string');
        $this->assertEquals('report_saldo', $intent, 'Should return first detected intent');
    }

    /**
     * Test triple intent: saldo + pengeluaran + pemasukan
     */
    public function test_triple_intent(): void
    {
        $message = 'cek saldo, pengeluaran, dan pemasukan bulan ini';
        $intents = $this->detector->detectMultiple($message);

        $this->assertContains('report_saldo', $intents);
        $this->assertContains('report_pengeluaran', $intents);
        $this->assertContains('report_pemasukan', $intents);
        $this->assertCount(3, $intents);
    }
}
