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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->date('tanggal')->nullable();
            $table->string('sakit')->nullable(); // e.g., 'hadir', 'sakit', 'izin', 'alfa'
            $table->string('izin')->nullable(); // e.g., 'hadir', 'sakit', 'izin', 'alfa'
            $table->string('tanpa_keterangan')->nullable(); // e.g., 'hadir', 'sakit', 'izin', 'alfa'
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
