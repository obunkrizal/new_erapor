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
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('npsn');
            $table->string('alamat');
            $table->string('no_telp');
            $table->string('email')->unique();
            $table->string('akreditasi');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->string('logo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
