<?php
require_once "vendor/autoload.php";

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FutureBudgetPlanningService;
use App\Models\WalletSetting;
use App\Models\User;

// Get user from DB
$user = User::first();
if (!$user) {
    file_put_contents('test_output.txt', "No user found\n");
    exit(1);
}

// Update user balance to 2.850.000
$wallet = WalletSetting::firstOrCreate(
    ['user_id' => $user->id],
    ['balance' => 0]
);
$wallet->balance = 2850000;
$wallet->save();

$output = "User ID: {$user->id}\n";
$output .= "User Balance: Rp " . number_format($wallet->balance, 0, ',', '.') . "\n\n";

// Test balance-based budget planning
$service = app(FutureBudgetPlanningService::class);

$output .= "=== Testing Balance-Based Budget Planning ===\n\n";
$response = $service->generate($user->id, null, 'bulan', 1, true);

$output .= $response;
$output .= "\n\n=== END ===\n";

file_put_contents('test_output.txt', $output);
echo "Output saved to test_output.txt\n";
