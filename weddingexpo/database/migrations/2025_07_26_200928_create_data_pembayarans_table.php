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
        Schema::create('data_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partisipasi_id')->constrained('partisipasis')->onDelete('cascade');
            $table->string('nama_pembayar');
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal_bayar');
            $table->string('metode_pembayaran')->default('Transfer Bank');
            $table->string('bukti_transfer');
            $table->foreignId('rekening_tujuan_id')->constrained('rekening_tujuans')->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pembayarans');
    }
};
