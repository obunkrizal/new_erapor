<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan', 100);
            $table->enum('rentang_usia', ['2-3', '4-5', '5-6'])->nullable();
            $table->string('jenis')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('rentang_usia');
        });

        Schema::create('siswa_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->text('capaian')->nullable(); // Auto-generated
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['siswa_id', 'ekstrakurikuler_id', 'periode_id'], 'siswa_ekskul_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('siswa_ekstrakurikuler');
        Schema::dropIfExists('ekstrakurikuler');
    }
};
