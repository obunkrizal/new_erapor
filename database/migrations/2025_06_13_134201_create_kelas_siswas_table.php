<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kelas_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')
                ->constrained('periodes')
                ->cascadeOnDelete();
            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->cascadeOnDelete();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Combined unique constraint for data integrity
            $table->unique(['periode_id', 'kelas_id', 'siswa_id'], 'kelas_siswa_unique');
            
            // Composite index for better query performance
            $table->index(['periode_id', 'kelas_id', 'siswa_id'], 'kelas_siswa_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kelas_siswas');
    }
};
