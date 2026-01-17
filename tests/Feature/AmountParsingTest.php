<?php

namespace Tests\Feature;

use App\Services\GoalSimulationService;
use Tests\TestCase;

class AmountParsingTest extends TestCase
{
    /**
     * Test parsing "harga X, uang jajan Y" - target dulu
     */
    public function test_parsing_harga_dulu_jajan_kemudian(): void
    {
        $message = 'saya ingin beli laptop harga 15jt, uang jajan sebulan 2jt';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(15000000, $result['target'], 'Target should be 15jt (laptop harga)');
        $this->assertEquals(2000000, $result['monthly'], 'Monthly should be 2jt (uang jajan sebulan)');
    }

    /**
     * Test parsing "uang jajan X, beli Y" - jajan dulu
     */
    public function test_parsing_jajan_dulu_target_kemudian(): void
    {
        $message = 'uang jajan saya 3jt sebulan, pengen beli motor 25jt';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(25000000, $result['target'], 'Target should be 25jt (motor)');
        $this->assertEquals(3000000, $result['monthly'], 'Monthly should be 3jt (uang jajan sebulan)');
    }

    /**
     * Test parsing "budget X, uang jajan Y"
     */
    public function test_parsing_budget_dan_jajan(): void
    {
        $message = 'ingin rakit pc budget 10jt, dengan uang jajan sebulan 2jt, kira kira berapa lama';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(10000000, $result['target'], 'Target should be 10jt (budget pc)');
        $this->assertEquals(2000000, $result['monthly'], 'Monthly should be 2jt (uang jajan sebulan)');
    }

    /**
     * Test parsing "harga X, gaji Y per bulan"
     */
    public function test_parsing_harga_dan_gaji(): void
    {
        $message = 'mau beli hp harga 8jt dengan gaji 4jt per bulan';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(8000000, $result['target'], 'Target should be 8jt (hp harga)');
        $this->assertEquals(4000000, $result['monthly'], 'Monthly should be 4jt (gaji per bulan)');
    }

    /**
     * Test parsing dengan format "seharga" dan "penghasilan bulanan"
     */
    public function test_parsing_seharga_dan_penghasilan(): void
    {
        $message = 'ingin punya laptop seharga 12jt, penghasilan bulanan 2.5jt';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(12000000, $result['target'], 'Target should be 12jt (seharga)');
        $this->assertEquals(2500000, $result['monthly'], 'Monthly should be 2.5jt (penghasilan bulanan)');
    }

    /**
     * Test parsing dengan format ribu (rb)
     */
    public function test_parsing_format_ribu(): void
    {
        $message = 'target 500rb, jajan per bulan 100rb';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(500000, $result['target'], 'Target should be 500rb');
        $this->assertEquals(100000, $result['monthly'], 'Monthly should be 100rb');
    }

    /**
     * Test parsing hanya dengan target (tanpa allowance)
     */
    public function test_parsing_hanya_target(): void
    {
        $message = 'mau beli laptop 10jt';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(10000000, $result['target'], 'Target should be 10jt');
        $this->assertNull($result['monthly'], 'Monthly should be null');
    }

    /**
     * Test parsing kompleks dengan banyak kata
     */
    public function test_parsing_kalimat_kompleks(): void
    {
        $message = 'saya mahasiswa dengan uang jajan per bulan 1.5jt, ingin beli iphone 16 yang harganya sekitar 20jt, kira-kira berapa lama ya?';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(20000000, $result['target'], 'Target should be 20jt (iphone harga)');
        $this->assertEquals(1500000, $result['monthly'], 'Monthly should be 1.5jt (uang jajan per bulan)');
    }

    /**
     * Test parsing dengan angka yang sama tapi konteks berbeda
     */
    public function test_parsing_angka_sama_konteks_berbeda(): void
    {
        $message = 'harga laptop 5jt, uang jajan 5jt sebulan';
        $result = GoalSimulationService::extractAmounts($message);

        // Keduanya 5jt, tapi konteks berbeda - yang pertama harus target
        $this->assertEquals(5000000, $result['target'], 'Target should be 5jt (harga)');
        $this->assertEquals(5000000, $result['monthly'], 'Monthly should be 5jt (uang jajan sebulan)');
    }

    /**
     * Test parsing dengan format "juta" lengkap
     */
    public function test_parsing_format_juta_lengkap(): void
    {
        $message = 'ingin beli motor seharga 25 juta dengan penghasilan 3 juta per bulan';
        $result = GoalSimulationService::extractAmounts($message);

        $this->assertEquals(25000000, $result['target'], 'Target should be 25 juta');
        $this->assertEquals(3000000, $result['monthly'], 'Monthly should be 3 juta');
    }
}
