<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Periode;

class PaudObservasiHarian extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏫 Ensuring Kelas & Siswa Data...');
        $this->ensureKelasAndSiswa();

        $this->command->info('👥 Using Existing Siswa Data...');
        $siswaIds = $this->getExistingSiswaIds();

        $this->command->info('📝 Seeding 150+ Observasi Harian (3 bulan)...');
        $this->seedObservasiHarian($siswaIds);

        $this->showObservasiSummary();
        $this->command->info('✅ OBSERVASI HARIAN SELESAI! Siap Generate Penilaian Semester!');
    }

    private function ensureKelasAndSiswa(): void
    {
        // Ensure Periode exists
        $periode = Periode::first();
        if (!$periode) {
            $periode = Periode::create([
                'nama_periode' => '2024/2025',
                'tahun_ajaran' => '2024/2025',
                'semester' => 'Ganjil',
                'tanggal_mulai' => '2024-07-01',
                'tanggal_selesai' => '2025-06-30',
                'status' => 'aktif',
            ]);
        }

        // Ensure Guru exists
        $guru = Guru::first();
        if (!$guru) {
            $guru = Guru::create([
                'user_id' => 1, // Assuming admin user exists
                'nama_lengkap' => 'Bu Sheena',
                'nip' => '1234567890',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1980-01-01',
                'alamat' => 'Jl. Sudirman No. 1',
                'telepon' => '081234567890',
                'email' => 'sheena@guru.com',
                'status' => 'aktif',
            ]);
        }

        // Ensure Kelas exist
        $kelasData = [
            ['nama_kelas' => 'Kelas PG', 'rentang_usia' => '2-3', 'kapasitas' => 5],
            ['nama_kelas' => 'Kelas A', 'rentang_usia' => '4-5', 'kapasitas' => 5],
            ['nama_kelas' => 'Kelas B', 'rentang_usia' => '5-6', 'kapasitas' => 5],
        ];

        foreach ($kelasData as $data) {
            $kelas = Kelas::where('nama_kelas', $data['nama_kelas'])->first();
            if (!$kelas) {
                $kelasAttributes = [
                    'nama_kelas' => $data['nama_kelas'],
                    'guru_id' => $guru->id,
                    'periode_id' => $periode->id,
                    'status' => 'aktif',
                ];

                // Add rentang_usia if column exists
                if (Schema::hasColumn('kelas', 'rentang_usia')) {
                    $kelasAttributes['rentang_usia'] = $data['rentang_usia'];
                }

                // Add kapasitas if column exists
                if (Schema::hasColumn('kelas', 'kapasitas')) {
                    $kelasAttributes['kapasitas'] = 5;
                }

                Kelas::create($kelasAttributes);
            }
        }

        // Ensure Siswa are assigned to Kelas
        $siswaWithoutKelas = Siswa::whereNull('kelas_id')->orWhere('status', '!=', 'Aktif')->get();
        $kelasList = Kelas::where('status', 'aktif')->get();

        if ($siswaWithoutKelas->count() > 0 && $kelasList->count() > 0) {
            foreach ($siswaWithoutKelas as $siswa) {
                // Assign to appropriate kelas based on age
                $usiaBulan = Carbon::parse($siswa->tanggal_lahir)->diffInMonths(now());
                $rentangUsia = match (true) {
                    $usiaBulan <= 36 => '2-3',
                    $usiaBulan <= 60 => '4-5',
                    default => '5-6'
                };

                $kelas = $kelasList->where('rentang_usia', $rentangUsia)->first();
                if ($kelas) {
                    $siswa->update([
                        'kelas_id' => $kelas->id,
                        'status' => 'Aktif'
                    ]);

                    // Create kelas_siswas record if not exists
                    DB::table('kelas_siswas')->updateOrInsert(
                        ['periode_id' => $periode->id, 'kelas_id' => $kelas->id, 'siswa_id' => $siswa->id],
                        [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ Kelas & Siswa data ensured successfully!');
    }

    private function getExistingSiswaIds(): array
    {
        // Ambil siswa yang sudah ada, aktif, dan memiliki kelas_id dari kelas yang aktif
        $siswaIds = DB::table('siswas')
            ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
            ->where('siswas.status', 'Aktif')
            ->where('kelas.status', 'aktif')
            ->whereNotNull('siswas.kelas_id')
            ->limit(5) // Ambil maksimal 5 siswa untuk testing
            ->pluck('siswas.id')
            ->toArray();

        if (empty($siswaIds)) {
            $this->command->error('Tidak ada siswa aktif dengan kelas aktif yang tersedia!');
            return [];
        }

        return $siswaIds;
    }

    private function seedObservasiHarian(array $siswaIds): void
    {
        $faker = Faker::create('id_ID');
        $startDate = Carbon::now()->subMonths(3)->startOfMonth(); // 3 bulan terakhir
        $endDate = Carbon::now();

        $kategoriPenilaian = ['BSB', 'BSH', 'MB', 'BB'];
        $catatanContoh = [
            'Sangat antusias mengikuti instruksi guru',
            'Dapat menulis namanya sendiri dengan huruf jelas',
            'Menghitung benda sampai 10 dengan akurat',
            'Berani memperkenalkan diri di depan kelas',
            'Bekerja sama baik dengan teman sekelompok',
            'Gerakan lincah dan koordinasi sangat baik',
            'Mengenali huruf A-Z dan membaca kata sederhana',
            'Mengamati tanaman dengan teliti dan bertanya',
            'Kreatif membuat bentuk dari playdough',
            'Bernyanyi dengan irama dan artikulasi jelas'
        ];

        $totalObservasi = 0;

        foreach ($siswaIds as $siswaId) {
            $siswa = DB::table('siswas')->find($siswaId);
            $usia = $this->getRentangUsiaFromSiswa($siswa->tanggal_lahir);

            // Ambil indikator sesuai usia siswa
            $indikatorIds = DB::table('indikator_capaian')
                ->where('rentang_usia', $usia)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id')
                ->toArray();

            // Generate 25-35 observasi per siswa (3 bulan)
            $observasiCount = rand(25, 35);

            // Get guru_id from kelas, default to 1 if not set
            $guruId = DB::table('kelas')->where('id', $siswa->kelas_id)->value('guru_id') ?? 1;

            for ($i = 0; $i < $observasiCount; $i++) {
                $tanggal = $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d');

                DB::table('observasi_harian')->insert([
                    'siswa_id' => $siswaId,
                    'guru_id' => $guruId,
                    'indikator_id' => $faker->randomElement($indikatorIds),
                    'kelas_id' => $siswa->kelas_id,
                    'tanggal_observasi' => $tanggal,
                    'kategori_penilaian' => $faker->randomElement($kategoriPenilaian),
                    'catatan_guru' => $faker->randomElement($catatanContoh) .
                        ' pada ' . Carbon::parse($tanggal)->format('d M Y'),
                    'foto_dokumentasi' => $faker->randomElement(['foto1.jpg', 'foto2.jpg', null]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $totalObservasi++;
            }
        }

        $this->command->info("Generated: {$totalObservasi} observasi harian");
    }

    private function getRentangUsiaFromSiswa(string $tanggalLahir): string
    {
        $usiaBulan = Carbon::parse($tanggalLahir)->diffInMonths(now());

        return match (true) {
            $usiaBulan <= 36 => '2-3',  // 0-3 tahun
            $usiaBulan <= 60 => '4-5',  // 3-5 tahun
            default => '5-6'            // 5-6 tahun
        };
    }

    private function showObservasiSummary(): void
    {
        $summary = DB::table('observasi_harian')
            ->selectRaw('
                siswas.nama_lengkap,
                siswas.tanggal_lahir,
                COUNT(*) as total_obs,
                GROUP_CONCAT(DISTINCT observasi_harian.kategori_penilaian) as kategori
            ')
            ->join('siswas', 'observasi_harian.siswa_id', '=', 'siswas.id')
            ->groupBy('observasi_harian.siswa_id', 'siswas.nama_lengkap', 'siswas.tanggal_lahir')
            ->orderBy('total_obs', 'desc')
            ->get();

        $distribusiKategori = DB::table('observasi_harian')
            ->selectRaw('kategori_penilaian, COUNT(*) as jumlah')
            ->groupBy('kategori_penilaian')
            ->orderBy('jumlah', 'desc')
            ->get();

        $this->command->table(
            ['Siswa', 'Usia', 'Observasi', 'Kategori'],
            $summary->map(function ($row) {
                $usia = Carbon::parse($row->tanggal_lahir)->age;
                return [$row->nama_lengkap, "{$usia} thn", $row->total_obs, $row->kategori];
            })
        );

        $this->command->newLine();
        $this->command->table(
            ['Kategori', 'Jumlah', '%'],
            $distribusiKategori->map(function ($row) {
                $persen = round(($row->jumlah / DB::table('observasi_harian')->count()) * 100, 1);
                return [$row->kategori_penilaian, $row->jumlah, "{$persen}%"];
            })
        );
    }
}
