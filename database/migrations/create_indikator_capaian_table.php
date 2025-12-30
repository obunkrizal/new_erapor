<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indikator_capaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimensi_id')->constrained('dimensi_pembelajaran')->onDelete('cascade');
            $table->string('kode_indikator', 30);
            $table->text('deskripsi');
            $table->enum('rentang_usia', ['2-3', '4-5', '5-6'])->default('5-6');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index(['dimensi_id', 'rentang_usia']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('indikator_capaian');
    }
};
