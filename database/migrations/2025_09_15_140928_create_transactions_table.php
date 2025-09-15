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
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // kasir
            $table->foreignId('cash_register_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->decimal('total_price', 15, 2);   // sebelum diskon/pajak
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);  // total - diskon + pajak
            $table->decimal('paid_amount', 15, 2);  // uang cash
            $table->decimal('change_amount', 15, 2);// kembalian
            $table->enum('status', ['paid','cancelled'])->default('paid');
            $table->timestamps();
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);    // harga per unit waktu transaksi
            $table->decimal('subtotal', 15, 2); // price * quantity
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
