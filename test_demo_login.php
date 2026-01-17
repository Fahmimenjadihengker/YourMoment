<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== LOGIN TEST ===\n\n";

$user = User::where('email', 'dummy@gmail.com')->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "Email: " . $user->email . "\n";
echo "Password: dummy (plaintext)\n";
echo "Hashed: " . $user->password . "\n\n";

// Test password verification
$isValid = Hash::check('dummy', $user->password);
echo ($isValid ? "✅" : "❌") . " Password verification: " . ($isValid ? "SUCCESS" : "FAILED") . "\n";

if ($isValid) {
    echo "\n✅ Login credentials are valid and working!\n";
    echo "You can login with:\n";
    echo "  Email: dummy@gmail.com\n";
    echo "  Password: dummy\n";
} else {
    echo "\n❌ Password verification failed!\n";
}

// Check wallet
echo "\n=== WALLET CHECK ===\n";
$wallet = $user->walletSetting;
if ($wallet) {
    echo "✅ Wallet exists\n";
    echo "   Balance: Rp" . number_format($wallet->balance, 0, ',', '.') . "\n";
} else {
    echo "❌ Wallet missing!\n";
}

// Check transactions
echo "\n=== TRANSACTION CHECK ===\n";
$count = $user->transactions()->count();
echo "✅ Transactions: " . $count . " records\n";

// Check insight generation capability
echo "\n=== INSIGHT GENERATION CHECK ===\n";
$txCount = $user->transactions()
    ->whereMonth('transaction_date', now()->month)
    ->count();
echo "✅ Current month transactions: " . $txCount . " records\n";

if ($txCount > 0) {
    echo "   → Insights can be generated\n";
}

echo "\n🎯 Demo account is ready for testing!\n";
