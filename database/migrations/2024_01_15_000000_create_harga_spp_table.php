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
        Schema::create('harga_spp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
            $table->string('tingkat_kelas')->nullable(); // 'X', 'XI', 'XII' or '10', '11', '12'
            $table->decimal('harga', 15, 2); // Price in rupiah
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure unique price per periode and class level
            $table->unique(['periode_id', 'kelas_id'], 'unique_periode_kelas');
            $table->unique(['periode_id', 'tingkat_kelas'], 'unique_periode_tingkat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_spp');
    }
};