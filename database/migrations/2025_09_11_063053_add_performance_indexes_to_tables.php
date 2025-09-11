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
        // Add compound index for nota_dinas_details
        Schema::table('nota_dinas_details', function (Blueprint $table) {
            $table->index(['nota_dinas_id', 'jenis_pengeluaran', 'vendor_id'], 'idx_nota_dinas_details_compound');
        });

        // Add compound index for expenses  
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['order_id', 'nota_dinas_detail_id'], 'idx_expenses_order_detail');
        });

        // Add compound index for orders
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'closing_date', 'created_at'], 'idx_orders_status_dates');
        });

        // Add index for data_pembayaran
        Schema::table('data_pembayaran', function (Blueprint $table) {
            $table->index(['order_id', 'tgl_bayar'], 'idx_data_pembayaran_order');
        });

        // Add index for products price fields  
        Schema::table('products', function (Blueprint $table) {
            $table->index(['product_price', 'pengurangan'], 'idx_products_price_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_dinas_details', function (Blueprint $table) {
            $table->dropIndex('idx_nota_dinas_details_compound');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_order_detail');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_dates');
        });

        Schema::table('data_pembayaran', function (Blueprint $table) {
            $table->dropIndex('idx_data_pembayaran_order');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_price_fields');
        });
    }
};
