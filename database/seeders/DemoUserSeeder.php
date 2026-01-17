<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WalletSetting;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\SavingGoal;
use App\Models\SavingContribution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get demo user (idempotent)
        $user = User::firstOrCreate(
            ['email' => 'dummy@gmail.com'],
            [
                'name' => 'Dummy User',
                'email_verified_at' => now(),
                'password' => bcrypt('dummy'),
                'remember_token' => null,
            ]
        );

        // Ensure wallet exists for demo user
        $wallet = WalletSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 1500000, // 1.5 juta - realistis untuk mahasiswa
                'monthly_allowance' => 500000,
                'weekly_allowance' => 125000,
                'financial_goal' => 5000000,
                'notes' => 'Demo account untuk testing',
            ]
        );

        // Ensure categories exist
        $this->ensureCategories();

        // Get or create demo transactions only if none exist for this month
        if ($this->shouldCreateTransactions($user)) {
            $this->createRealisticTransactions($user, $wallet);
        }

        // Create demo saving goals
        $this->createDemoSavingGoals($user);
    }

    /**
     * Create demo saving goals for demo user
     */
    private function createDemoSavingGoals(User $user): void
    {
        // Only create if none exist
        if (SavingGoal::where('user_id', $user->id)->exists()) {
            return;
        }

        $now = Carbon::now();

        // Saving Goal 1: Laptop Baru (in progress, 45%)
        $laptop = SavingGoal::create([
            'user_id' => $user->id,
            'name' => 'Laptop Baru',
            'description' => 'Upgrade laptop untuk kerja dan kuliah',
            'target_amount' => 8000000,
            'current_amount' => 3600000,
            'deadline' => $now->clone()->addMonths(4)->format('Y-m-d'),
            'icon' => '💻',
            'color' => '#3b82f6',
            'status' => 'active',
            'priority' => 'high',
        ]);

        // Contributions for laptop
        $contributions = [
            ['amount' => 500000, 'note' => 'Tabungan awal', 'days_ago' => 45],
            ['amount' => 800000, 'note' => 'Dari bonus kerja partime', 'days_ago' => 30],
            ['amount' => 600000, 'note' => 'Sisihan uang bulanan', 'days_ago' => 21],
            ['amount' => 700000, 'note' => 'Uang lebaran', 'days_ago' => 14],
            ['amount' => 500000, 'note' => 'Bonus tugas', 'days_ago' => 7],
            ['amount' => 500000, 'note' => 'Sisihan mingguan', 'days_ago' => 3],
        ];

        foreach ($contributions as $contrib) {
            SavingContribution::create([
                'saving_goal_id' => $laptop->id,
                'amount' => $contrib['amount'],
                'note' => $contrib['note'],
                'contributed_at' => $now->clone()->subDays($contrib['days_ago']),
            ]);
        }

        // Saving Goal 2: Dana Darurat (low progress, 20%)
        $emergency = SavingGoal::create([
            'user_id' => $user->id,
            'name' => 'Dana Darurat',
            'description' => '3 bulan pengeluaran untuk jaga-jaga',
            'target_amount' => 3000000,
            'current_amount' => 600000,
            'deadline' => $now->clone()->addMonths(6)->format('Y-m-d'),
            'icon' => '🏥',
            'color' => '#ef4444',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        SavingContribution::create([
            'saving_goal_id' => $emergency->id,
            'amount' => 400000,
            'note' => 'Mulai dana darurat',
            'contributed_at' => $now->clone()->subDays(20),
        ]);

        SavingContribution::create([
            'saving_goal_id' => $emergency->id,
            'amount' => 200000,
            'note' => 'Tambahan mingguan',
            'contributed_at' => $now->clone()->subDays(7),
        ]);

        // Saving Goal 3: Liburan (medium progress, 60%)
        $vacation = SavingGoal::create([
            'user_id' => $user->id,
            'name' => 'Liburan ke Bali',
            'description' => 'Liburan akhir semester',
            'target_amount' => 2000000,
            'current_amount' => 1200000,
            'deadline' => $now->clone()->addMonths(2)->format('Y-m-d'),
            'icon' => '✈️',
            'color' => '#10b981',
            'status' => 'active',
            'priority' => 'low',
        ]);

        SavingContribution::create([
            'saving_goal_id' => $vacation->id,
            'amount' => 500000,
            'note' => 'Tabungan liburan',
            'contributed_at' => $now->clone()->subDays(25),
        ]);

        SavingContribution::create([
            'saving_goal_id' => $vacation->id,
            'amount' => 400000,
            'note' => 'Bonus THR',
            'contributed_at' => $now->clone()->subDays(15),
        ]);

        SavingContribution::create([
            'saving_goal_id' => $vacation->id,
            'amount' => 300000,
            'note' => 'Sisihan bulanan',
            'contributed_at' => $now->clone()->subDays(5),
        ]);

        // Saving Goal 4: Completed Goal (for history)
        $headphones = SavingGoal::create([
            'user_id' => $user->id,
            'name' => 'Headphones Sony',
            'description' => 'Headphones untuk WFH',
            'target_amount' => 500000,
            'current_amount' => 500000,
            'deadline' => $now->clone()->subDays(10)->format('Y-m-d'),
            'icon' => '🎧',
            'color' => '#8b5cf6',
            'status' => 'completed',
            'priority' => 'low',
        ]);

        SavingContribution::create([
            'saving_goal_id' => $headphones->id,
            'amount' => 250000,
            'note' => 'Setengah target',
            'contributed_at' => $now->clone()->subDays(30),
        ]);

        SavingContribution::create([
            'saving_goal_id' => $headphones->id,
            'amount' => 250000,
            'note' => 'Goal tercapai! 🎉',
            'contributed_at' => $now->clone()->subDays(12),
        ]);
    }

    /**
     * Ensure all demo categories exist
     */
    private function ensureCategories(): void
    {
        $categories = [
            // Expense categories
            ['name' => 'Makanan', 'type' => 'expense', 'icon' => '🍔', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => '🚗', 'color' => '#3b82f6'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => '☕', 'color' => '#ec4899'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => '📚', 'color' => '#8b5cf6'],
            ['name' => 'Kebutuhan', 'type' => 'expense', 'icon' => '🛍️', 'color' => '#f59e0b'],
            ['name' => 'Lainnya', 'type' => 'expense', 'icon' => '📌', 'color' => '#6b7280'],
            
            // Income categories
            ['name' => 'Gaji/Uang Bulanan', 'type' => 'income', 'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => '🎁', 'color' => '#06b6d4'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                $category
            );
        }
    }

    /**
     * Check if we should create transactions (only create for current month if empty)
     */
    private function shouldCreateTransactions(User $user): bool
    {
        $currentMonth = Carbon::now()->format('Y-m');
        
        return !Transaction::where('user_id', $user->id)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->exists();
    }

    /**
     * Create realistic transaction data for demo
     */
    private function createRealisticTransactions(User $user, WalletSetting $wallet): void
    {
        $now = Carbon::now();
        $startOfMonth = $now->clone()->startOfMonth();
        $endOfMonth = $now->clone()->endOfMonth();

        // Get categories
        $categories = Category::all()->keyBy('name');

        // Transaction data structure: [date_offset, type, amount, category_name, description]
        $transactions = [
            // PEMASUKAN (Income) - Early month
            [2, 'income', 2000000, 'Gaji/Uang Bulanan', 'Uang bulanan bulan ini'],
            [15, 'income', 500000, 'Bonus', 'Bonus dari tugas tambahan'],

            // PENGELUARAN (Expenses) - Distributed throughout month
            // Makanan - paling banyak
            [3, 'expense', 35000, 'Makanan', 'Sarapan di warung'],
            [3, 'expense', 25000, 'Makanan', 'Siang di kantin'],
            [4, 'expense', 40000, 'Makanan', 'Makan malam'],
            [5, 'expense', 15000, 'Makanan', 'Kopi'],
            [6, 'expense', 50000, 'Makanan', 'Makan siang bareng'],
            [7, 'expense', 30000, 'Makanan', 'Makanan kosan'],
            [8, 'expense', 25000, 'Makanan', 'Snack'],
            [9, 'expense', 45000, 'Makanan', 'Makan malam keluarga'],
            [10, 'expense', 35000, 'Makanan', 'Makan siang'],
            [12, 'expense', 55000, 'Makanan', 'Pesanan online'],
            [14, 'expense', 30000, 'Makanan', 'Kopi pagi'],
            [16, 'expense', 40000, 'Makanan', 'Makan dengan teman'],
            [18, 'expense', 50000, 'Makanan', 'Pesanan makanan'],
            [20, 'expense', 35000, 'Makanan', 'Sarapan'],
            [22, 'expense', 45000, 'Makanan', 'Makan malam'],

            // Transportasi
            [3, 'expense', 20000, 'Transportasi', 'Bensin motor'],
            [10, 'expense', 20000, 'Transportasi', 'Bensin'],
            [17, 'expense', 25000, 'Transportasi', 'Transportasi online'],
            [24, 'expense', 20000, 'Transportasi', 'Bensin motor'],

            // Hiburan
            [5, 'expense', 60000, 'Hiburan', 'Nonton bioskop'],
            [12, 'expense', 40000, 'Hiburan', 'Main game arcade'],
            [19, 'expense', 50000, 'Hiburan', 'Konser musik online'],

            // Pendidikan
            [4, 'expense', 75000, 'Pendidikan', 'Beli buku pelajaran'],
            [11, 'expense', 100000, 'Pendidikan', 'Kursus online'],

            // Kebutuhan
            [6, 'expense', 120000, 'Kebutuhan', 'Beli sabun dan shampoo'],
            [13, 'expense', 80000, 'Kebutuhan', 'Beli pakaian'],
            [21, 'expense', 50000, 'Kebutuhan', 'Peralatan mandi'],

            // Lainnya
            [8, 'expense', 25000, 'Lainnya', 'Amal ke masjid'],
            [15, 'expense', 35000, 'Lainnya', 'Hadiah untuk teman'],
            [23, 'expense', 40000, 'Lainnya', 'Kontribusi acara'],
        ];

        // Create transactions
        foreach ($transactions as [$offset, $type, $amount, $categoryName, $description]) {
            $transactionDate = $startOfMonth->clone()->addDays($offset);

            // Skip if date is beyond end of month
            if ($transactionDate->greaterThan($endOfMonth)) {
                continue;
            }

            $category = $categories[$categoryName] ?? null;
            if (!$category) {
                continue;
            }

            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'transaction_date' => $transactionDate->format('Y-m-d'),
                'payment_method' => $this->getRandomPaymentMethod(),
            ]);
        }

        // Update wallet balance to reflect transactions
        // Balance = total income - total expense (NO phantom initial balance)
        $totalIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->sum('amount');

        // Set balance to actual calculated value
        $wallet->balance = $totalIncome - $totalExpense;
        $wallet->save();
    }

    /**
     * Get random payment method for realism
     */
    private function getRandomPaymentMethod(): string
    {
        $methods = ['cash', 'transfer', 'e-wallet', 'kartu kredit', 'debit'];
        return $methods[array_rand($methods)];
    }
}
