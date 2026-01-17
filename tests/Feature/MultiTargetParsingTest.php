<?php

namespace Tests\Feature;

use App\Services\GoalSimulationService;
use App\Services\ChatSessionMemoryService;
use Tests\TestCase;

class MultiTargetParsingTest extends TestCase
{
    /**
     * Test parsing single target
     */
    public function test_parse_single_target(): void
    {
        $message = 'ingin beli laptop 10jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertCount(1, $targets);
        $this->assertStringContainsString('laptop', $targets[0]['name']);
        $this->assertEquals(10000000, $targets[0]['amount']);
    }

    /**
     * Test parsing multiple targets - laptop dan hp
     */
    public function test_parse_multiple_targets_laptop_hp(): void
    {
        $message = 'laptop 7jt dan hp 4jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertCount(2, $targets);

        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertEquals(11000000, $total);
    }

    /**
     * Test parsing multiple targets with more items
     */
    public function test_parse_multiple_targets_three_items(): void
    {
        $message = 'mau beli laptop 10jt, hp 5jt, sama tablet 3jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertGreaterThanOrEqual(2, count($targets));

        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertGreaterThanOrEqual(15000000, $total);
    }

    /**
     * Test calculate total target
     */
    public function test_calculate_total_target(): void
    {
        $targets = [
            ['name' => 'laptop', 'amount' => 7000000],
            ['name' => 'hp', 'amount' => 4000000],
        ];

        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertEquals(11000000, $total);
    }

    /**
     * Test format targets for display
     */
    public function test_format_targets_for_display(): void
    {
        $targets = [
            ['name' => 'laptop', 'amount' => 7000000],
            ['name' => 'hp', 'amount' => 4000000],
        ];

        $formatted = GoalSimulationService::formatTargetsForDisplay($targets);

        $this->assertStringContainsString('Laptop', $formatted);
        $this->assertStringContainsString('Hp', $formatted);
        $this->assertStringContainsString('7.000.000', $formatted);
        $this->assertStringContainsString('4.000.000', $formatted);
    }

    /**
     * Test parsing target with different format
     */
    public function test_parse_target_format_variations(): void
    {
        // Format "motor 25 juta"
        $message = 'pengen motor 25 juta';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertCount(1, $targets);
        $this->assertStringContainsString('motor', $targets[0]['name']);
        $this->assertEquals(25000000, $targets[0]['amount']);
    }

    /**
     * Test no duplicate targets
     */
    public function test_no_duplicate_targets(): void
    {
        $message = 'laptop 7jt, laptop bagus harga 7jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        // Should only have one laptop entry (may include modifier)
        $laptopCount = count(array_filter($targets, fn($t) => strpos($t['name'], 'laptop') !== false));
        $this->assertEquals(1, $laptopCount);
    }

    /**
     * BUG FIX TEST: ipad dan macbook m3 pro
     * "saya mau beli ipad 7jt dan macbook m3 pro 20jt, gaji saya 5jt perbulan"
     */
    public function test_parse_ipad_and_macbook_with_modifier(): void
    {
        $message = 'saya mau beli ipad 7jt dan macbook m3 pro 20jt, gaji saya 5jt perbulan';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        // Should have 2 targets
        $this->assertCount(2, $targets, 'Should extract exactly 2 targets');

        // Calculate total
        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertEquals(27000000, $total, 'Total should be 27jt (7jt + 20jt)');

        // Check ipad exists
        $hasIpad = collect($targets)->contains(fn($t) => strpos($t['name'], 'ipad') !== false && $t['amount'] === 7000000);
        $this->assertTrue($hasIpad, 'Should have ipad with 7jt');

        // Check macbook exists  
        $hasMacbook = collect($targets)->contains(fn($t) => strpos($t['name'], 'macbook') !== false && $t['amount'] === 20000000);
        $this->assertTrue($hasMacbook, 'Should have macbook with 20jt');
    }

    /**
     * Test parsing with product modifiers
     */
    public function test_parse_with_product_modifiers(): void
    {
        $message = 'beli iphone 15 pro max 25jt sama macbook air m2 18jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertCount(2, $targets);

        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertEquals(43000000, $total);
    }

    /**
     * Test that monthly income is NOT counted as target
     */
    public function test_monthly_income_not_counted_as_target(): void
    {
        $message = 'beli laptop 15jt dengan gaji 5jt perbulan';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        // Should only have laptop, not gaji
        $this->assertCount(1, $targets);
        $this->assertStringContainsString('laptop', $targets[0]['name']);
        $this->assertEquals(15000000, $targets[0]['amount']);
    }

    /**
     * Test complex sentence with multiple targets and income
     */
    public function test_complex_sentence_multi_target_with_income(): void
    {
        $message = 'target saya laptop gaming 20jt dan monitor 5jt, penghasilan bulanan 4jt';
        $targets = GoalSimulationService::extractMultipleTargets($message);

        $this->assertGreaterThanOrEqual(1, count($targets));

        // Total should NOT include penghasilan
        $total = GoalSimulationService::calculateTotalTarget($targets);
        $this->assertGreaterThanOrEqual(5000000, $total);
        $this->assertLessThanOrEqual(25000000, $total);
    }
}
