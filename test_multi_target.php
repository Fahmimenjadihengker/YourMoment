<?php
require_once 'vendor/autoload.php';

use App\Services\GoalSimulationService;

$message = 'saya mau beli ipad 7jt dan macbook m3 pro 20jt, gaji saya 5jt perbulan';
echo "Input: $message\n\n";

$targets = GoalSimulationService::extractMultipleTargets($message);

echo "Targets found: " . count($targets) . "\n";
foreach ($targets as $t) {
    echo "- {$t['name']}: " . number_format($t['amount'], 0, ',', '.') . "\n";
}

$total = GoalSimulationService::calculateTotalTarget($targets);
echo "\nTotal: " . number_format($total, 0, ',', '.') . "\n";

echo "\n--- Test 2 ---\n";
$message2 = 'iphone 15 pro max 25jt sama macbook air m2 18jt';
echo "Input: $message2\n\n";

$targets2 = GoalSimulationService::extractMultipleTargets($message2);
echo "Targets found: " . count($targets2) . "\n";
foreach ($targets2 as $t) {
    echo "- {$t['name']}: " . number_format($t['amount'], 0, ',', '.') . "\n";
}
$total2 = GoalSimulationService::calculateTotalTarget($targets2);
echo "Total: " . number_format($total2, 0, ',', '.') . "\n";
