<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Siswa;
use Faker\Factory as Faker;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $agama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'];
        $pekerjaan = ['Tidak Bekerja', 'Petani', 'Buruh', 'PNS', 'TNI', 'Polri', 'Pedagang', 'Wiraswasta', 'Karyawan Swasta', 'Lainnya'];
        $pekerjaanIbu = ['Tidak Bekerja', 'Petani', 'Buruh', 'PNS', 'TNI', 'Polri', 'Pedagang', 'Wiraswasta', 'Karyawan Swasta', 'Ibu Rumah Tangga', 'Lainnya'];
        $pendidikan = ['Tidak Sekolah', 'SD/MI', 'SMP/MTs', 'SMA/MA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];

        for ($i = 1; $i <= 10; $i++) {
            $jenisKelamin = $faker->randomElement(['L', 'P']);
            $nama_lengkap = $jenisKelamin == 'L' ?
            $faker->firstNameMale : $faker->firstNameFemale;
            $nama_lengkap .= ' ' . $faker->lastName;

            Siswa::create([
                'nisn' => $faker->unique()->numerify('##########'),
                'nis' => $faker->unique()->numerify('######'),
                'nama_lengkap' => $nama_lengkap,
                'nik' => $faker->unique()->numerify('################'),
                'kk' => $faker->unique()->numerify('################'),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
                'jenis_kelamin' => $jenisKelamin,
                'agama' => $faker->randomElement($agama),
                'nama_ayah' => $faker->firstNameMale . ' ' . $faker->lastName,
                'nama_ibu' => $faker->firstNameFemale . ' ' . $faker->lastName,
                'pekerjaan_ayah' => $faker->randomElement($pekerjaan),
                'pekerjaan_ibu' => $faker->randomElement($pekerjaanIbu),
                'pendidikan_ayah' => $faker->randomElement($pendidikan),
                'pendidikan_ibu' => $faker->randomElement($pendidikan),
                'telepon' => $faker->phoneNumber,
                'alamat' => $faker->address,
                'provinsi_id' => $faker->numberBetween(1, 34),
                'kota_id' => $faker->numberBetween(1, 100),
                'kecamatan_id' => $faker->numberBetween(1, 500),
                'kelurahan_id' => $faker->numberBetween(1, 1000),
                'foto' => null,
                'status' => $faker->randomElement(['Aktif', 'Tidak Aktif']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
