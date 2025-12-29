<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('template_narasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimensi_id')->constrained('dimensi_pembelajaran')->onDelete('cascade');
            $table->enum('kategori_penilaian', ['BB', 'MB', 'BSH', 'BSB']);
            $table->text('template_kalimat'); // {nama} dapat {kemampuan} dengan {kualitas}
            $table->json('placeholder_options')->nullable(); // {"kemampuan": ["mengenal huruf", "menulis nama"], "kualitas": ["baik", "sangat baik"]}
            $table->timestamps();

            $table->unique(['dimensi_id', 'kategori_penilaian']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('template_narasi');
    }
};