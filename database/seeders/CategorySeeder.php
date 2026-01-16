<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Expense Categories
        $expenseCategories = [
            ['name' => 'Makan', 'type' => 'expense', 'icon' => '🍔', 'color' => '#f97316'],
            ['name' => 'Transport', 'type' => 'expense', 'icon' => '🚗', 'color' => '#3b82f6'],
            ['name' => 'Nongkrong', 'type' => 'expense', 'icon' => '☕', 'color' => '#ec4899'],
            ['name' => 'Akademik', 'type' => 'expense', 'icon' => '📚', 'color' => '#8b5cf6'],
            ['name' => 'Lainnya', 'type' => 'expense', 'icon' => '📌', 'color' => '#6b7280'],
        ];

        // Income Categories
        $incomeCategories = [
            ['name' => 'Gaji', 'type' => 'income', 'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'Beasiswa', 'type' => 'income', 'icon' => '🎓', 'color' => '#06b6d4'],
        ];

        // Merge both
        $allCategories = array_merge($expenseCategories, $incomeCategories);

        // Insert categories
        foreach ($allCategories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                $category
            );
        }
    }
}
