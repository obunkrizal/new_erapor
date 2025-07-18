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
        Schema::create('surat_keputusan_guru', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis_keputusan', [
                'pengangkatan',
                'promosi',
                'mutasi',
                'pemberhentian',
                'penugasan_khusus'
            ]);
            $table->enum('status_kepegawaian', [
                'pns',
                'pppk',
                'gtk_honorer',
                'kontrak',
                'gty',
                'gtt'
            ]);
            
            $table->string('jabatan_lama')->nullable();
            $table->string('jabatan_baru');
            $table->string('unit_kerja_lama')->nullable();
            $table->string('unit_kerja_baru');
            $table->date('tmt_berlaku'); // Terhitung Mulai Tanggal
            $table->date('tmt_berakhir')->nullable();
            $table->text('dasar_hukum');
            $table->text('pertimbangan');
            $table->text('isi_keputusan');
            $table->string('pejabat_penandatangan');
            $table->string('jabatan_penandatangan');
            $table->string('nip_penandatangan')->nullable();
            $table->string('file_surat')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'published', 'cancelled'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_persetujuan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['guru_id', 'jenis_keputusan']);
            $table->index(['status', 'tanggal_surat']);
            $table->index('tmt_berlaku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keputusan_guru');
    }
};
