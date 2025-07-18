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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique()->nullable();
            $table->string('nis')->unique();
            $table->string('nama_lengkap');
            $table->string('nik')->unique()->nullable();
            $table->string('kk')->unique()->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('agama',[
                'Islam',
                'Kristen',
                'Katolik',
                'Hindu',
                'Budha',
                'Konghucu',
            ])
            ->default('Islam')
            ->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->enum('pekerjaan_ayah',[
                'Tidak Bekerja',
                'Petani',
                'Buruh',
                'PNS',
                'TNI',
                'Polri',
                'Pedagang',
                'Wiraswasta',
                'Karyawan Swasta',
                'Lainnya'
            ])->nullable();
            $table->enum('pekerjaan_ibu',[
                'Tidak Bekerja',
                'Petani',
                'Buruh',
                'PNS',
                'TNI',
                'Polri',
                'Pedagang',
                'Wiraswasta',
                'Karyawan Swasta',
                'Ibu Rumah Tangga',
                'Lainnya'
            ])->nullable();
            $table->enum('pendidikan_ayah',[
                'Tidak Sekolah',
                'SD/MI',
                'SMP/MTs',
                'SMA/MA',
                'D1',
                'D2',
                'D3',
                'S1',
                'S2',
                'S3'
            ])->nullable();
            $table->enum('pendidikan_ibu',[
                'Tidak Sekolah',
                'SD/MI',
                'SMP/MTs',
                'SMA/MA',
                'D1',
                'D2',
                'D3',
                'S1',
                'S2',
                'S3'
            ])->nullable();
            $table->string('telepon')->nullable();
            $table->string('alamat')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('kelurahan_id')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->foreignId('kelas_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
