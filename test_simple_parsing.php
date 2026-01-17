<?php

/**
 * Simple test untuk parsing nominal
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\GoalSimulationService;

echo "=== TEST PARSING NOMINAL ===\n\n";

// Test case yang bermasalah
$message = 'saya ingin beli laptop harga 15jt, uang jajan sebulan 2jt';
echo "Message: \"$message\"\n\n";

try {
    $result = GoalSimulationService::extractAmounts($message);

    echo "Hasil parsing:\n";
    echo "- Target: " . ($result['target'] ? number_format($result['target']) : 'null') . "\n";
    echo "- Monthly: " . ($result['monthly'] ? number_format($result['monthly']) : 'null') . "\n";
    echo "- Weekly: " . ($result['weekly'] ? number_format($result['weekly']) : 'null') . "\n\n";

    // Expected
    echo "Expected:\n";
    echo "- Target: 15,000,000 (15jt = harga laptop)\n";
    echo "- Monthly: 2,000,000 (2jt = uang jajan sebulan)\n\n";

    // Verify
    $targetOK = $result['target'] === 15000000;
    $monthlyOK = $result['monthly'] === 2000000;

    echo "Status:\n";
    echo "- Target: " . ($targetOK ? "PASS" : "FAIL (got " . $result['target'] . ")") . "\n";
    echo "- Monthly: " . ($monthlyOK ? "PASS" : "FAIL (got " . $result['monthly'] . ")") . "\n";

    echo "\n=== TEST CASE LAINNYA ===\n\n";

    $testCases = [
        'uang jajan saya 3jt sebulan, pengen beli motor 25jt' => [25000000, 3000000],
        'mau beli hp harga 8jt dengan gaji 4jt per bulan' => [8000000, 4000000],
        'ingin rakit pc budget 10jt, dengan uang jajan sebulan 2jt' => [10000000, 2000000],
    ];

    foreach ($testCases as $msg => $expected) {
        $r = GoalSimulationService::extractAmounts($msg);
        $tOK = $r['target'] === $expected[0];
        $mOK = $r['monthly'] === $expected[1];

        $status = ($tOK && $mOK) ? "PASS" : "FAIL";
        echo "$status: \"$msg\"\n";
        echo "   Got: target=" . number_format($r['target'] ?? 0) . ", monthly=" . number_format($r['monthly'] ?? 0) . "\n";
        echo "   Expected: target=" . number_format($expected[0]) . ", monthly=" . number_format($expected[1]) . "\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
