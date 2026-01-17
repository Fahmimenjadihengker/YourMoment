<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Saving Goals Table
        Schema::create('saving_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name'); // Nama target, e.g., "Beli iPhone"
            $table->text('description')->nullable();
            $table->decimal('target_amount', 12, 2); // Target nominal
            $table->decimal('current_amount', 12, 2)->default(0); // Terkumpul
            $table->date('deadline')->nullable(); // Deadline (opsional)
            $table->string('icon', 10)->default('🎯'); // Emoji icon
            $table->string('color', 7)->default('#10b981'); // Hex color
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('deadline');
        });

        // Saving Contributions Table (riwayat kontribusi)
        Schema::create('saving_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_goal_id')->constrained('saving_goals')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('contributed_at');
            $table->timestamps();

            // Index
            $table->index('contributed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_contributions');
        Schema::dropIfExists('saving_goals');
    }
};
