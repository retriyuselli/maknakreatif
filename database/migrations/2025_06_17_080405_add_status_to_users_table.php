<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserStatusEnum; // Pastikan Anda mengimpor Enum Anda

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom 'status' setelah kolom 'password'
            // Anda bisa membuatnya nullable atau memberikan nilai default
            $table->string('status')
                  ->nullable() // Atau ->default(UserStatusEnum::KARYAWAN->value) jika Anda ingin default
                  ->after('password'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
