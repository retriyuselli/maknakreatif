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
        Schema::table('expense_ops', function (Blueprint $table) {
            // NotaDinas integration fields
            $table->unsignedBigInteger('nota_dinas_id')->nullable()->after('kategori_transaksi');
            $table->unsignedBigInteger('nota_dinas_detail_id')->nullable()->after('nota_dinas_id');
            $table->unsignedBigInteger('vendor_id')->nullable()->after('nota_dinas_detail_id');
            
            // Bank transfer fields
            $table->string('bank_name')->nullable()->after('vendor_id');
            $table->string('account_holder')->nullable()->after('bank_name');
            $table->string('bank_account')->nullable()->after('account_holder');
            $table->date('tanggal_transfer')->nullable()->after('bank_account');
            
            // Foreign key constraints
            $table->foreign('nota_dinas_id')->references('id')->on('nota_dinas')->onDelete('set null');
            $table->foreign('nota_dinas_detail_id')->references('id')->on('nota_dinas_details')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_ops', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['nota_dinas_id']);
            $table->dropForeign(['nota_dinas_detail_id']);
            $table->dropForeign(['vendor_id']);
            
            // Drop columns
            $table->dropColumn([
                'nota_dinas_id',
                'nota_dinas_detail_id',
                'vendor_id',
                'bank_name',
                'account_holder',
                'bank_account',
                'tanggal_transfer'
            ]);
        });
    }
};
