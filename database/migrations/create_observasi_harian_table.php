<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('observasi_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('indikator_id')->constrained('indikator_capaian')->onDelete('cascade');
            $table->date('tanggal_observasi');
            $table->enum('kategori_penilaian', ['BB', 'MB', 'BSH', 'BSB']);
            $table->text('catatan_guru')->nullable();
            $table->string('foto_dokumentasi')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal_observasi']);
            $table->index(['indikator_id', 'kategori_penilaian']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('observasi_harian');
    }
};
