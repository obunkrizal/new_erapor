<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SuratKeputusanGuru;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuratKeputusanGuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SuratKeputusanGuru::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get all users to use as guru and approver
        $users = User::all();
        
        if ($users->count() < 2) {
            $this->command->warn('Please run UserSeeder first to create users');
            return;
        }

        $jenisKeputusan = ['pengangkatan', 'promosi', 'mutasi', 'pemberhentian', 'penugasan', 'sanksi'];
        $statusKepegawaian = ['pns', 'pppk', 'honorer', 'kontrak','gty','gtt'];
        $statusSurat = ['draft', 'review', 'approved', 'published', 'cancelled'];
        
        $jabatanList = [
            'Guru Kelas I',
            'Guru Kelas II',
            'Guru Kelas III',
            'Guru Kelas IV',
            'Guru Kelas V',
            'Guru Kelas VI',
            'Guru Matematika',
            'Guru Bahasa Indonesia',
            'Guru Bahasa Inggris',
            'Guru IPA',
            'Guru IPS',
            'Guru Olahraga',
            'Guru Seni Budaya',
            'Wali Kelas',
            'Koordinator Mata Pelajaran',
            'Wakil Kepala Sekolah',
        ];

        $unitKerjaList = [
            'SD Negeri 1 Jakarta',
            'SD Negeri 2 Jakarta',
            'SD Negeri 3 Jakarta',
            'SMP Negeri 1 Jakarta',
            'SMP Negeri 2 Jakarta',
            'SMA Negeri 1 Jakarta',
            'SMA Negeri 2 Jakarta',
            'SMK Negeri 1 Jakarta',
        ];

        $this->command->info('Creating Surat Keputusan Guru records...');

        // Create 50 sample records
        for ($i = 1; $i <= 50; $i++) {
            try {
                $tanggalSurat = Carbon::now()->subDays(rand(1, 365));
                $jenis = $jenisKeputusan[array_rand($jenisKeputusan)];
                $guru = $users->random();
                $approver = $users->where('id', '!=', $guru->id)->random();

                $record = SuratKeputusanGuru::create([
                    'tanggal_surat' => $tanggalSurat,
                    'perihal' => $this->generatePerihal($jenis, $guru->name),
                    'guru_id' => $guru->id,
                    'jenis_keputusan' => $jenis,
                    'status_kepegawaian' => $statusKepegawaian[array_rand($statusKepegawaian)],
                    'jabatan_lama' => $jabatanList[array_rand($jabatanList)],
                    'jabatan_baru' => $jabatanList[array_rand($jabatanList)],
                    'unit_kerja_lama' => $unitKerjaList[array_rand($unitKerjaList)],
                    'unit_kerja_baru' => $unitKerjaList[array_rand($unitKerjaList)],
                    'tmt_berlaku' => $tanggalSurat->copy()->addDays(rand(7, 30)),
                    'tmt_berakhir' => rand(0, 1) ? $tanggalSurat->copy()->addYears(rand(1, 3)) : null,
                    'dasar_hukum' => $this->generateDasarHukum(),
                    'pertimbangan' => $this->generatePertimbangan($jenis),
                    'isi_keputusan' => $this->generateIsiKeputusan($jenis, $guru->name),
                    'pejabat_penandatangan' => 'Dr. ' . fake()->name(),
                    'jabatan_penandatangan' => 'Kepala Dinas Pendidikan',
                    'nip_penandatangan' => fake()->numerify('##########'),
                    'status' => $statusSurat[array_rand($statusSurat)],
                    'catatan' => rand(0, 1) ? fake()->sentence() : null,
                    'tanggal_persetujuan' => rand(0, 1) ? $tanggalSurat->copy()->addDays(rand(1, 7)) : null,
                    'disetujui_oleh' => rand(0, 1) ? $approver->id : null,
                    'created_at' => $tanggalSurat,
                    'updated_at' => $tanggalSurat,
                ]);

                $this->command->info("Created record {$i}/50: {$record->nomor_surat}");

            } catch (\Exception $e) {
                $this->command->error("Error creating record {$i}: " . $e->getMessage());
                // Continue with next record
                continue;
            }
        }

        $this->command->info('SuratKeputusanGuru seeder completed successfully!');
    }

    private function generatePerihal($jenis, $namaGuru): string
    {
        return match($jenis) {
            'pengangkatan' => "Pengangkatan {$namaGuru} sebagai Guru",
            'promosi' => "Promosi Jabatan {$namaGuru}",
            'mutasi' => "Mutasi {$namaGuru}",
            'pemberhentian' => "Pemberhentian {$namaGuru}",
            'penugasan' => "Penugasan Khusus {$namaGuru}",
            'sanksi' => "Sanksi Administratif {$namaGuru}",
            default => "Keputusan terkait {$namaGuru}",
        };
    }

    private function generateDasarHukum(): string
    {
        return "1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n" .
               "2. Undang-Undang Nomor 14 Tahun 2005 tentang Guru dan Dosen;\n" .
               "3. Peraturan Pemerintah Nomor 74 Tahun 2008 tentang Guru;\n" .
               "4. Peraturan Menteri Pendidikan dan Kebudayaan Nomor 15 Tahun 2018.";
    }

    private function generatePertimbangan($jenis): string
    {
        return match($jenis) {
            'pengangkatan' => "Bahwa untuk memenuhi kebutuhan tenaga pendidik yang berkualitas dan profesional, perlu dilakukan pengangkatan guru yang memenuhi kualifikasi dan kompetensi yang dipersyaratkan.",
            'promosi' => "Bahwa berdasarkan penilaian kinerja dan prestasi yang telah dicapai, yang bersangkutan layak untuk dipromosikan ke jabatan yang lebih tinggi.",
            'mutasi' => "Bahwa untuk kepentingan dinas dan pemerataan tenaga pendidik, perlu dilakukan mutasi guru ke unit kerja yang membutuhkan.",
            'pemberhentian' => "Bahwa berdasarkan pertimbangan tertentu dan sesuai dengan ketentuan yang berlaku, perlu dilakukan pemberhentian terhadap yang bersangkutan.",
            'penugasan' => "Bahwa untuk mendukung program pendidikan dan meningkatkan kualitas pembelajaran, perlu diberikan penugasan khusus kepada yang bersangkutan.",
            'sanksi' => "Bahwa berdasarkan hasil pemeriksaan dan sesuai dengan ketentuan disiplin pegawai, perlu diberikan sanksi administratif kepada yang bersangkutan.",
            default => "Bahwa berdasarkan pertimbangan dinas dan kepentingan pendidikan, perlu ditetapkan keputusan ini.",
        };
    }

    private function generateIsiKeputusan($jenis, $namaGuru): string
    {
        return match($jenis) {
            'pengangkatan' => "MEMUTUSKAN:\n\nKESATU: Mengangkat Saudara {$namaGuru} sebagai Guru dengan tugas dan tanggung jawab sesuai dengan ketentuan yang berlaku.\n\nKEDUA: Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.",
            'promosi' => "MEMUTUSKAN:\n\nKESATU: Mempromosikan Saudara {$namaGuru} ke jabatan yang lebih tinggi sesuai dengan kualifikasi dan kompetensi yang dimiliki.\n\nKEDUA: Yang bersangkutan wajib melaksanakan tugas dan tanggung jawab sesuai dengan jabatan baru yang diberikan.",
            'mutasi' => "MEMUTUSKAN:\n\nKESATU: Memutasikan Saudara {$namaGuru} ke unit kerja baru sesuai dengan kebutuhan dinas.\n\nKEDUA: Yang bersangkutan wajib melaporkan diri ke unit kerja baru sesuai dengan TMT yang ditetapkan.",
            'pemberhentian' => "MEMUTUSKAN:\n\nKESATU: Memberhentikan Saudara {$namaGuru} dari jabatan sebagai Guru sesuai dengan ketentuan yang berlaku.\n\nKEDUA: Yang bersangkutan wajib menyerahkan semua tugas dan tanggung jawab kepada pejabat yang ditunjuk.",
            'penugasan' => "MEMUTUSKAN:\n\nKESATU: Memberikan penugasan khusus kepada Saudara {$namaGuru} untuk melaksanakan tugas tambahan sesuai dengan kebutuhan dinas.\n\nKEDUA: Penugasan ini berlaku untuk jangka waktu tertentu sesuai dengan ketentuan yang ditetapkan.",
            'sanksi' => "MEMUTUSKAN:\n\nKESATU: Memberikan sanksi administratif kepada Saudara {$namaGuru} sesuai dengan pelanggaran yang dilakukan.\n\nKEDUA: Yang bersangkutan wajib memperbaiki kinerja dan tidak mengulangi pelanggaran yang sama.",
            default => "MEMUTUSKAN:\n\nKESATU: Menetapkan keputusan terkait Saudara {$namaGuru} sesuai dengan ketentuan yang berlaku.\n\nKEDUA: Keputusan ini berlaku sejak tanggal ditetapkan.",
        };
    }
}