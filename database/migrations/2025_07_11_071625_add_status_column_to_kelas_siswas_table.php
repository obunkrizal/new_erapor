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
        Schema::table('kelas_siswas', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'pindah', 'lulus', 'keluar'])
                ->default('aktif')
                ->after('siswa_id');
            $table->date('tanggal_masuk')
                ->nullable()
                ->after('status');
            $table->text('keterangan')
                ->nullable()
                ->after('tanggal_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_siswas', function (Blueprint $table) {
            //
        });
    }
};
