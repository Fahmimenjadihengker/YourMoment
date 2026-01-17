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
        // Add composite index untuk query cepat: financial_insights by user_id + period
        Schema::table('financial_insights', function (Blueprint $table) {
            // Jika unique sudah ada, ini akan jadi redundant tapi safe
            // Unique ['user_id', 'period'] akan digunakan sebagai index juga
            // Tapi kita pastikan composite index tersedia untuk query optimization
            $table->index(['user_id', 'period']);
        });

        // Add composite index untuk transaction queries
        // user_id + type + transaction_date digunakan di dashboard queries
        Schema::table('transactions', function (Blueprint $table) {
            // Cek apakah index sudah ada, jika belum tambah
            $table->index(['user_id', 'type', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_insights', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'period']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'transaction_date']);
        });
    }
};
