<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Periode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Kelas::class, 'kelas_id');
            $table->foreignIdFor(Guru::class, 'guru_id');
            $table->foreignIdFor(Siswa::class, 'siswa_id');
            $table->foreignIdFor(Periode::class, 'periode_id');
            $table->text('nilai_agama')->nullable();
            $table->text('nilai_jatiDiri')->nullable();
            $table->text('nilai_literasi')->nullable();
            $table->text('nilai_narasi')->nullable();
            $table->text('refleksi_guru')->nullable();
            $table->string('fotoAgama')->nullable();
            $table->string('fotoJatiDiri')->nullable();
            $table->string('fotoLiterasi')->nullable();
            $table->string('fotoNarasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
