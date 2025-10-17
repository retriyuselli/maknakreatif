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
        Schema::table('bank_statements', function (Blueprint $table) {
            // Add separate file path for reconciliation Excel file
            $table->string('reconciliation_file')->nullable()->after('file_path');
            
            // Add separate original filename for reconciliation file
            $table->string('reconciliation_original_filename')->nullable()->after('original_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_statements', function (Blueprint $table) {
            $table->dropColumn(['reconciliation_file', 'reconciliation_original_filename']);
        });
    }
};
