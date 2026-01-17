<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds time column to transactions table for more precise tracking.
     * Existing transactions will have time set to 00:00:00.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add time column after transaction_date
            $table->time('transaction_time')->default('00:00:00')->after('transaction_date');
        });

        // Update existing transactions to use created_at time if available
        DB::statement("
            UPDATE transactions 
            SET transaction_time = TIME(created_at) 
            WHERE transaction_time = '00:00:00'
        ");

        // Update the index to include time for better query performance
        Schema::table('transactions', function (Blueprint $table) {
            // Drop old index if exists
            $table->dropIndex(['user_id', 'transaction_date']);
            
            // Create new composite index including time
            $table->index(['user_id', 'transaction_date', 'transaction_time'], 'transactions_user_datetime_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop new index
            $table->dropIndex('transactions_user_datetime_index');
            
            // Restore old index
            $table->index(['user_id', 'transaction_date']);
            
            // Remove time column
            $table->dropColumn('transaction_time');
        });
    }
};
