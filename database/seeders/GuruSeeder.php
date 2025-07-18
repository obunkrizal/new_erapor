<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Periode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        // Create active periode if not exists
        $periode = Periode::firstOrCreate([
            'nama_periode' => 'Semester 1 2024/2025',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Ganjil',
            'is_active' => true,
        ]);

        // Create guru users
        $guruData = [
            [
                'name' => 'Siti Nurhaliza',
                'nip' => '198501012010012001',
                'nama_guru' => 'Siti Nurhaliza, S.Pd',
                'telepon' => '081234567890',
                'alamat' => 'Jl. Pendidikan No. 123',
                'jenis_kelamin' => 'P',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'nip' => '198502022010011002',
                'nama_guru' => 'Ahmad Fauzi, S.Pd',
                'telepon' => '081234567891',
                'alamat' => 'Jl. Guru No. 456',
                'jenis_kelamin' => 'L',
            ],
            [
                'name' => 'Dewi Sartika',
                'nip' => '198503032010012003',
                'nama_guru' => 'Dewi Sartika, S.Pd',
                'telepon' => '081234567892',
                'alamat' => 'Jl. Pahlawan No. 789',
                'jenis_kelamin' => 'P',
            ]
        ];

        foreach ($guruData as $data) {
            // Create user
            // $user = User::create([
            //     'name' => $data['name'],
            //     'email' => $data['email'],
            //     'password' => Hash::make('password123'),
            //     'role' => 'guru',
            //     'email_verified_at' => now(),
            // ]);

            // Create guru profile
            $guru = Guru::create([
                // 'user_id' => $user->id,
                'nama_guru' => $data['nama_guru'],
                'nip' => $data['nip'],
                'telepon' => $data['telepon'],
                'alamat' => $data['alamat'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'status' => 'aktif',
            ]);

            // Create classes for this guru
            // foreach ($data['kelas'] as $namaKelas) {
            //     Kelas::create([
            //         'nama_kelas' => $namaKelas,
            //         'guru_id' => $guru->id,
            //         'periode_id' => $periode->id,
            //         'status' => 'aktif',
            //         'kapasitas' => 5,
            //     ]);
            // }
        }

        if ($this->command) {
            $this->command->info('Guru users and classes created successfully!');
        }
    }
}
