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
        Schema::table('kelas', function (Blueprint $table) {
            $table->enum('rentang_usia',['2-3','3-4','4-5','5-6'])->nullable()->after('nama_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Drop the 'rentang_usia' column if it exists
            if (Schema::hasColumn('kelas', 'rentang_usia')) {
                $table->dropColumn('rentang_usia');
            }
        });
    }
};
