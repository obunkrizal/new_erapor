<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dimensi_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique(); // LIT, MAT, SAI, JD, SE, FM, dll
            $table->string('nama', 100);
            $table->enum('kategori', [
                'dasar_literasi_matematika_sains',
                'jati_diri', 
                'nilai_agama_budi_pekerti'
            ]);
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dimensi_pembelajaran');
    }
};