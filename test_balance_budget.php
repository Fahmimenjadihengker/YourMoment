<?php
require_once "vendor/autoload.php";

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FutureBudgetPlanningService;
use App\Models\WalletSetting;
use App\Models\User;

// Get user from DB (or create test user)
$user = User::first();
if (!$user) {
    echo "No user found, creating test...\n";
    exit(1);
}

// Update user balance to 2.850.000
$wallet = WalletSetting::firstOrCreate(
    ['user_id' => $user->id],
    ['balance' => 0]
);
$wallet->balance = 2850000;
$wallet->save();

echo "User ID: {$user->id}\n";
echo "User Balance: Rp " . number_format($wallet->balance, 0, ',', '.') . "\n\n";

// Test balance-based budget planning
$service = app(FutureBudgetPlanningService::class);

echo "=== Testing Balance-Based Budget Planning ===\n\n";
$response = $service->generate($user->id, null, 'bulan', 1, true);

echo $response;
