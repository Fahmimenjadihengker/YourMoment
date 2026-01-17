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
        Schema::create('financial_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period', 7); // Format: YYYY-MM
            $table->text('summary_text'); // Teks insight yang disimpan
            $table->string('source', 20)->default('fallback'); // 'ai' atau 'fallback'
            $table->json('summary_data')->nullable(); // Data summary untuk referensi
            $table->timestamps();

            // Unique constraint: 1 insight per user per bulan
            $table->unique(['user_id', 'period']);
            
            // Index untuk query cepat
            $table->index('period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_insights');
    }
};
