<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WalletSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user first
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => null,
        ]);

        // Create wallet setting for test user
        WalletSetting::create([
            'user_id' => $user->id,
            'balance' => 500000,
            'monthly_allowance' => 100000,
            'weekly_allowance' => 25000,
            'financial_goal' => 1000000,
            'notes' => 'Uang jajan mahasiswa',
        ]);

        // Seed categories
        $this->call(CategorySeeder::class);
    }
}
