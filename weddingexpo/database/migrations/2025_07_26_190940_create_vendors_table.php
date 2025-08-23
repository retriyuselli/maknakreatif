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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor');
            $table->foreignId('jenis_usaha_id')->constrained('jenis_usahas')->onDelete('cascade');
            $table->text('alamat');
            $table->string('kota');
            $table->string('no_telepon');
            $table->string('email')->unique();
            $table->string('nama_pic');
            $table->string('no_wa_pic');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
