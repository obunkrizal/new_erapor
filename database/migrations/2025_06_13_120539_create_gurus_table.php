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
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_guru');
            $table->string('nip')->nullable();
            $table->string('nuptk')->nullable();
            $table->enum('jenis_kelamin', [
                'L',
                'P',
            ])->default('L');
            $table->enum('agama', [
                'Islam',
                'Kristen',
                'Katolik',
                'Hindu',
                'Budha',
                'Konghucu',
            ])->default('Islam')->nullable();
            $table->enum('jabatan', [
                'Guru Kelas',
                'Guru Mapel',
                'Kepala Sekolah',
                'Waka Kurikulum',
                'Waka Kesiswaan',
                'Waka Humas',
                'Waka Sarana Prasarana',
                'Waka Hubungan Masyarakat',
                'Lainnya'
            ])->default('Guru Mapel')->nullable();
            $table->enum('pendidikan_terakhir', [
                'SD',
                'SMP',
                'SMA',
                'D1',
                'D2',
                'D3',
                'S1',
                'S2',
                'S3'
            ])->default('S1')->nullable();
            $table->enum('status_kepegawaian', [
                'PNS',
                'Non PNS',
                'Honorer',
                'GTY',
                'GTT',
            ])->default('GTY')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('telepon')->nullable();
            $table->string('alamat')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('kelurahan_id')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', [
                'Aktif',
                'Tidak Aktif',
            ])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
