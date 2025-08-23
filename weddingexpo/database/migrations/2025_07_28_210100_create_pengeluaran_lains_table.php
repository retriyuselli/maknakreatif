<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran_lains', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengeluaran');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal');
            $table->foreignId('rekening_tujuan_id')->constrained('rekening_tujuans')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_lains');
    }
};
