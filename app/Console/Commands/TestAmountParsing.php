<?php

namespace App\Console\Commands;

use App\Services\GoalSimulationService;
use Illuminate\Console\Command;

class TestAmountParsing extends Command
{
    protected $signature = 'test:parsing';
    protected $description = 'Test amount parsing dari message';

    public function handle()
    {
        $this->info("=== TEST PARSING NOMINAL BERDASARKAN KONTEKS ===\n");

        $testCases = [
            // [message, expected_target, expected_monthly, description]

            // BUG CASE - ini yang harus diperbaiki
            [
                'saya ingin beli laptop harga 15jt, uang jajan sebulan 2jt',
                15000000,
                2000000,
                'BUG CASE: harga dulu, jajan kemudian'
            ],

            // Normal case - target di depan
            [
                'ingin rakit pc budget 10jt, dengan uang jajan sebulan 2jt, kira kira berapa lama',
                10000000,
                2000000,
                'NORMAL: budget dulu, jajan kemudian'
            ],

            // Reversed - jajan di depan, target di belakang
            [
                'uang jajan saya 3jt sebulan, pengen beli motor 25jt',
                25000000,
                3000000,
                'REVERSED: jajan dulu, target kemudian'
            ],

            // Variasi dengan "harga"
            [
                'mau beli hp harga 8jt dengan gaji 4jt per bulan',
                8000000,
                4000000,
                'VARIASI: hp harga X, gaji Y'
            ],
        ];

        $passed = 0;
        $failed = 0;

        foreach ($testCases as $index => $test) {
            [$message, $expectedTarget, $expectedMonthly, $description] = $test;

            $result = GoalSimulationService::extractAmounts($message);

            $targetMatch = $result['target'] === $expectedTarget;
            $monthlyMatch = $result['monthly'] === $expectedMonthly;

            if ($targetMatch && $monthlyMatch) {
                $this->info("✅ Test #" . ($index + 1) . ": {$description}");
                $passed++;
            } else {
                $this->error("❌ Test #" . ($index + 1) . ": {$description}");
                $this->line("   Message: \"{$message}\"");
                $this->line("   Expected: target=" . number_format($expectedTarget) . ", monthly=" . number_format($expectedMonthly));
                $this->line("   Got:      target=" . number_format($result['target'] ?? 0) . ", monthly=" . number_format($result['monthly'] ?? 0));
                $failed++;
            }
        }

        $this->newLine();
        $this->info("========================================");
        $this->info("SUMMARY: {$passed} passed, {$failed} failed");
        $this->info("========================================");

        return $failed > 0 ? 1 : 0;
    }
}
