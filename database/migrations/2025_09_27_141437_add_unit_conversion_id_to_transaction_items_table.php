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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_conversion_id')->nullable()->after('product_id');
            $table->foreign('unit_conversion_id')->references('id')->on('product_unit_conversions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign('transaction_items_unit_conversion_id_foreign');
            $table->dropColumn('unit_conversion_id');
        });
    }
};
