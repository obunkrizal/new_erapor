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
            // First, drop the existing incorrect foreign key constraint
            $table->dropForeign(['guru_id']);
        });

        // Now add the correct foreign key constraint
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('guru_id')
                  ->references('id')
                  ->on('gurus')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Drop the correct foreign key
            $table->dropForeign(['guru_id']);
            
            // Restore the original (incorrect) foreign key
            $table->foreign('guru_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};