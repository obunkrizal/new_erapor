<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pindah_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_asal_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('kelas_tujuan_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who processed the transfer
            $table->date('tanggal_pindah');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('alasan_pindah');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pindah_kelas');
    }
};
