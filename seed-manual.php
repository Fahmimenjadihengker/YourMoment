<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

// Manual database seeding script
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Seed Categories
$categories = [
    // Expense
    ['name' => 'Makan', 'type' => 'expense', 'icon' => '🍔', 'color' => '#f97316'],
    ['name' => 'Transport', 'type' => 'expense', 'icon' => '🚗', 'color' => '#3b82f6'],
    ['name' => 'Nongkrong', 'type' => 'expense', 'icon' => '☕', 'color' => '#ec4899'],
    ['name' => 'Akademik', 'type' => 'expense', 'icon' => '📚', 'color' => '#8b5cf6'],
    ['name' => 'Lainnya', 'type' => 'expense', 'icon' => '📌', 'color' => '#6b7280'],
    // Income
    ['name' => 'Gaji', 'type' => 'income', 'icon' => '💼', 'color' => '#10b981'],
    ['name' => 'Beasiswa', 'type' => 'income', 'icon' => '🎓', 'color' => '#06b6d4'],
];

echo "Seeding categories...\n";
foreach ($categories as $cat) {
    Category::updateOrCreate(
        ['name' => $cat['name'], 'type' => $cat['type']],
        $cat
    );
}
echo "Categories seeded!\n";

// Seed User
echo "Creating test user...\n";
$user = User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);
echo "User created!\n";

// Seed Wallet Setting
echo "Creating wallet setting...\n";
$user->walletSetting()->create([
    'balance' => 500000,
    'monthly_allowance' => 100000,
    'weekly_allowance' => 25000,
    'financial_goal' => 1000000,
    'notes' => 'Uang jajan mahasiswa',
]);
echo "Wallet setting created!\n";

echo "✅ All seeding completed!\n";
