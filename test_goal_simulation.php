<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\ChatIntentDetector;
use App\Services\GoalSimulationService;

echo "=== Testing Goal Simulation ===\n\n";

$detector = new ChatIntentDetector();
$simulator = new GoalSimulationService();

$testCases = [
    // Goal simulation cases
    'saya ingin rakit pc budget 10jt, dengan uang jajan sebulan 2jt. kira kira berapa lama agar bisa tercapai',
    'mau beli iPhone 15jt, nabung 500rb per minggu, kapan bisa kebeli',
    'target beli motor 20jt, gaji 5jt sebulan, berapa bulan ya',
    'pengen punya laptop 8jt, sisihkan 1jt tiap bulan, kira-kira berapa lama',
    'ingin liburan budget 5jt, uang jajan 1.5jt per bulan, kapan tercapai',

    // Non goal simulation (should be recommendation/report)
    'rekomendasi pengeluaran makan',
    'berapa saldo saya',
    'tips hemat dong',
];

echo "--- Intent Detection ---\n\n";

foreach ($testCases as $message) {
    $intent = $detector->detect($message);

    if ($intent === 'goal_simulation') {
        $icon = '🎯';
    } elseif ($intent === 'recommendation') {
        $icon = '💡';
    } else {
        $icon = '📊';
    }

    echo "{$icon} Intent: {$intent}\n";
    echo "   \"{$message}\"\n";

    if ($intent === 'goal_simulation') {
        $data = $detector->extractGoalSimulationData($message);
        echo "   → Target: " . ($data['target'] ? number_format($data['target']) : 'null') . "\n";
        echo "   → Monthly: " . ($data['monthly'] ? number_format($data['monthly']) : 'null') . "\n";
        echo "   → Weekly: " . ($data['weekly'] ? number_format($data['weekly']) : 'null') . "\n";
    }

    echo "\n";
}

echo "--- Simulation Test ---\n\n";

// Test simulation
$response = $simulator->simulate(10000000, 2000000);
echo "Target: 10jt, Nabung: 2jt/bulan\n";
echo $response . "\n\n";

echo "=== Test Complete ===\n";
