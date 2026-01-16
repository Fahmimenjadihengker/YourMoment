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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->enum('type', ['income', 'expense']); // tipe transaksi
            $table->decimal('amount', 12, 2); // nominal
            $table->text('description')->nullable(); // keterangan
            $table->date('transaction_date'); // tanggal transaksi
            $table->string('payment_method')->nullable(); // cash, transfer, dll
            $table->timestamps();

            // Index untuk query yang lebih cepat
            $table->index(['user_id', 'transaction_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
