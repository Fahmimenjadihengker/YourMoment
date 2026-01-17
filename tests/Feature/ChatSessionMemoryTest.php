<?php

namespace Tests\Feature;

use App\Services\ChatSessionMemoryService;
use Tests\TestCase;

class ChatSessionMemoryTest extends TestCase
{
    protected ChatSessionMemoryService $memory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memory = new ChatSessionMemoryService();
        // Clear any existing state
        $this->memory->clearPendingState();
    }

    protected function tearDown(): void
    {
        // Clean up
        $this->memory->clearPendingState();
        parent::tearDown();
    }

    /**
     * Test default state
     */
    public function test_default_state(): void
    {
        $this->assertFalse($this->memory->isAwaitingIncomeInput());
        $this->assertEmpty($this->memory->getPendingTargets());
        $this->assertEquals(0, $this->memory->getPendingTargetTotal());
    }

    /**
     * Test set pending targets
     */
    public function test_set_pending_targets(): void
    {
        $targets = [
            ['name' => 'laptop', 'amount' => 7000000],
            ['name' => 'hp', 'amount' => 4000000],
        ];

        $this->memory->setPendingTargets($targets);

        $this->assertEquals($targets, $this->memory->getPendingTargets());
        $this->assertEquals(11000000, $this->memory->getPendingTargetTotal());
    }

    /**
     * Test awaiting income input
     */
    public function test_awaiting_income_input(): void
    {
        $this->assertFalse($this->memory->isAwaitingIncomeInput());

        $this->memory->setAwaitingIncomeInput(true);
        $this->assertTrue($this->memory->isAwaitingIncomeInput());

        $this->memory->setAwaitingIncomeInput(false);
        $this->assertFalse($this->memory->isAwaitingIncomeInput());
    }

    /**
     * Test save pending goal simulation
     */
    public function test_save_pending_goal_simulation(): void
    {
        $targets = [
            ['name' => 'laptop', 'amount' => 10000000],
        ];

        $this->memory->savePendingGoalSimulation($targets);

        $this->assertTrue($this->memory->isAwaitingIncomeInput());
        $this->assertEquals($targets, $this->memory->getPendingTargets());
        $this->assertEquals('goal_simulation', $this->memory->getLastIntent());
    }

    /**
     * Test has pending goal simulation
     */
    public function test_has_pending_goal_simulation(): void
    {
        $this->assertFalse($this->memory->hasPendingGoalSimulation());

        $this->memory->savePendingGoalSimulation([
            ['name' => 'laptop', 'amount' => 10000000],
        ]);

        $this->assertTrue($this->memory->hasPendingGoalSimulation());
    }

    /**
     * Test clear pending state
     */
    public function test_clear_pending_state(): void
    {
        $this->memory->savePendingGoalSimulation([
            ['name' => 'laptop', 'amount' => 10000000],
        ]);

        $this->assertTrue($this->memory->hasPendingGoalSimulation());

        $this->memory->clearPendingState();

        $this->assertFalse($this->memory->isAwaitingIncomeInput());
        $this->assertEmpty($this->memory->getPendingTargets());
        $this->assertNull($this->memory->getLastIntent());
    }

    /**
     * Test format pending targets string - single
     */
    public function test_format_pending_targets_string_single(): void
    {
        $this->memory->setPendingTargets([
            ['name' => 'laptop', 'amount' => 10000000],
        ]);

        $formatted = $this->memory->formatPendingTargetsString();

        $this->assertStringContainsString('Laptop', $formatted);
        $this->assertStringContainsString('10.000.000', $formatted);
    }

    /**
     * Test format pending targets string - multiple
     */
    public function test_format_pending_targets_string_multiple(): void
    {
        $this->memory->setPendingTargets([
            ['name' => 'laptop', 'amount' => 7000000],
            ['name' => 'hp', 'amount' => 4000000],
        ]);

        $formatted = $this->memory->formatPendingTargetsString();

        $this->assertStringContainsString('Laptop', $formatted);
        $this->assertStringContainsString('Hp', $formatted);
        $this->assertStringContainsString('dan', $formatted);
    }
}
