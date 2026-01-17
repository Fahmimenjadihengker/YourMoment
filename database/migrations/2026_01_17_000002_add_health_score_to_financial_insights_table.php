<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom health_score dan health_label ke tabel financial_insights.
     * Backward-compatible: semua kolom nullable.
     */
    public function up(): void
    {
        Schema::table('financial_insights', function (Blueprint $table) {
            $table->unsignedTinyInteger('health_score')->nullable()->after('source');
            $table->string('health_label', 50)->nullable()->after('health_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_insights', function (Blueprint $table) {
            $table->dropColumn(['health_score', 'health_label']);
        });
    }
};
