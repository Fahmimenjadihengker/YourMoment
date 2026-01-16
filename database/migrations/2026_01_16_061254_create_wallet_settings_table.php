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
        Schema::create('wallet_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0); // total balance
            $table->decimal('monthly_allowance', 10, 2)->nullable(); // uang jajan per bulan
            $table->decimal('weekly_allowance', 10, 2)->nullable(); // uang jajan per minggu
            $table->decimal('financial_goal', 12, 2)->nullable(); // target saving
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_settings');
    }
};
