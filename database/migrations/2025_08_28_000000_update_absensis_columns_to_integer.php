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
        Schema::table('absensis', function (Blueprint $table) {
            $table->integer('sakit')->nullable()->change();
            $table->integer('izin')->nullable()->change();
            $table->integer('tanpa_keterangan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('sakit')->nullable()->change();
            $table->string('izin')->nullable()->change();
            $table->string('tanpa_keterangan')->nullable()->change();
        });
    }
};
