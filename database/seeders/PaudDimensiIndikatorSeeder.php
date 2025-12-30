<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaudDimensiIndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧹 Resetting PAUD tables...');
        $this->resetTables();

        $this->command->info('🌱 Seeding 10 Dimensi PAUD...');
        $this->seedDimensiPembelajaran();

        $this->command->info('📋 Seeding Indikator 3 Rentang Usia...');
        $this->seedIndikatorAllUsia();

        $this->command->info('✍️ Seeding Template Narasi...');
        $this->seedTemplateNarasi();

        $this->showSummaryByUsia();
        $this->command->info('🎉 PAUD FULL DATABASE (Playgroup + TK A + TK B) SELESAI!');
    }

    private function resetTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('SET SQL_SAFE_UPDATES = 0');

        DB::table('observasi_harian')->delete();
        DB::table('penilaian_semester')->delete();
        DB::table('template_narasi')->delete();
        DB::table('indikator_capaian')->delete();
        DB::table('dimensi_pembelajaran')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        DB::statement('SET SQL_SAFE_UPDATES = 1');
    }

    private function seedDimensiPembelajaran(): void
    {
        $dimensi = [
            ['kode' => 'NAG', 'nama' => 'Nilai Agama', 'kategori' => 'nilai_agama_budi_pekerti', 'urutan' => 1],
            ['kode' => 'BP', 'nama' => 'Budi Pekerti', 'kategori' => 'nilai_agama_budi_pekerti', 'urutan' => 2],
            ['kode' => 'JD', 'nama' => 'Identitas Diri', 'kategori' => 'jati_diri', 'urutan' => 3],
            ['kode' => 'SE', 'nama' => 'Sosial Emosional', 'kategori' => 'jati_diri', 'urutan' => 4],
            ['kode' => 'FM', 'nama' => 'Fisik Motorik', 'kategori' => 'jati_diri', 'urutan' => 5],
            ['kode' => 'LIT', 'nama' => 'Literasi', 'kategori' => 'dasar_literasi_matematika_sains', 'urutan' => 6],
            ['kode' => 'MAT', 'nama' => 'Matematika', 'kategori' => 'dasar_literasi_matematika_sains', 'urutan' => 7],
            ['kode' => 'SAI', 'nama' => 'Sains', 'kategori' => 'dasar_literasi_matematika_sains', 'urutan' => 8],
            ['kode' => 'TEK', 'nama' => 'Teknologi & Rekayasa', 'kategori' => 'dasar_literasi_matematika_sains', 'urutan' => 9],
            ['kode' => 'SEN', 'nama' => 'Seni', 'kategori' => 'dasar_literasi_matematika_sains', 'urutan' => 10],
        ];

        foreach ($dimensi as $data) {
            DB::table('dimensi_pembelajaran')->insertOrIgnore([
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'kategori' => $data['kategori'],
                'deskripsi' => $this->getDeskripsiDimensi($data['kode']),
                'urutan' => $data['urutan'],
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function seedIndikatorAllUsia(): void
    {
        // 🎯 1. PLAYGROUP (2-3 tahun) - DASAR BANGET
        $playgroup = $this->getPlaygroupIndikator();
        $this->insertIndikatorBatch($playgroup, '2-3');

        // 🎯 2. TK A (4-5 tahun) - TRANSISI
        $tka = $this->getTKAIndikator();
        $this->insertIndikatorBatch($tka, '4-5');

        // 🎯 3. TK B (5-6 tahun) - Lanjutan
        $tkb = $this->getTKBIndikator();
        $this->insertIndikatorBatch($tkb, '5-6');
    }

    private function getPlaygroupIndikator(): array
    {
        return [
            // NAG - Playgroup (3)
            ['NAG', 'NAG-PG01', 'Ikut berdoa sederhana dengan guru (lipat tangan)', 1],
            ['NAG', 'NAG-PG02', 'Ucap "makasih" saat dikasih makan', 2],
            ['NAG', 'NAG-PG03', 'Kasih mainan ke teman saat diminta', 3],

            // BP - Playgroup (3)
            ['BP', 'BP-PG01', 'Bilang "tolong" saat minta benda', 1],
            ['BP', 'BP-PG02', 'Kembaliin mainan ke tempatnya', 2],
            ['BP', 'BP-PG03', 'Duduk rapi saat mendengarkan cerita', 3],

            // JD - Playgroup (3)
            ['JD', 'JD-PG01', 'Kenali nama diri sendiri', 1],
            ['JD', 'JD-PG02', 'Tunjuk hidung, mata, mulut', 2],
            ['JD', 'JD-PG03', 'Senyum & lambaikan tangan saat disapa', 3],

            // SE - Playgroup (3)
            ['SE', 'SE-PG01', 'Ikut tepuk tangan bareng teman', 1],
            ['SE', 'SE-PG02', 'Main paralel dekat teman (tidak rebutan)', 2],
            ['SE', 'SE-PG03', 'Cemberut saat lapar/capek (ekspresi dasar)', 3],

            // FM - Playgroup (3)
            ['FM', 'FM-PG01', 'Jalan sambil pegangan tangan', 1],
            ['FM', 'FM-PG02', 'Ambil benda besar dengan kedua tangan', 2],
            ['FM', 'FM-PG03', 'Susun balok 2-3 tingkat', 3],

            // LIT - Playgroup (3)
            ['LIT', 'LIT-PG01', 'Lihat & tunjuk gambar familiar (bola, rumah)', 1],
            ['LIT', 'LIT-PG02', 'Tirukan bunyi hewan (meong, gonggong)', 2],
            ['LIT', 'LIT-PG03', 'Ikut nyanyi lagu sederhana', 3],

            // MAT - Playgroup (2)
            ['MAT', 'MAT-PG01', 'Kenali "satu" & "banyak"', 1],
            ['MAT', 'MAT-PG02', 'Susun balok besar-kecil', 2],

            // SAI - Playgroup (2)
            ['SAI', 'SAI-PG01', 'Sentuh air (basah/kering)', 1],
            ['SAI', 'SAI-PG02', 'Lihat daun jatuh (atas/bawah)', 2],

            // SEN - Playgroup (2)
            ['SEN', 'SEN-PG01', 'Coret-coret dengan crayon', 1],
            ['SEN', 'SEN-PG02', 'Goyang badan ikut musik', 2],
        ];
    }

    private function getTKAIndikator(): array
    {
        return [
            // NAG - TK A (4-5 thn) (4)
            ['NAG', 'NAG-TA01', 'Berdoa sendiri sebelum/sesudah makan', 1],
            ['NAG', 'NAG-TA02', 'Ucap syukur atas nikmat Tuhan', 2],
            ['NAG', 'NAG-TA03', 'Bersihkan meja setelah makan', 3],
            ['NAG', 'NAG-TA04', 'Hormati teman beda agama', 4],

            // BP - TK A (4)
            ['BP', 'BP-TA01', 'Pakai 2-3 kata sopan (tolong, makasih, maaf)', 1],
            ['BP', 'BP-TA02', 'Ngaku salah saat dilaporkan guru', 2],
            ['BP', 'BP-TA03', 'Berbagi mainan tanpa diminta', 3],
            ['BP', 'BP-TA04', 'Antri saat ambil makanan/mainan', 4],

            // JD - TK A (4)
            ['JD', 'JD-TA01', 'Sebut nama lengkap & nama mama/papa', 1],
            ['JD', 'JD-TA02', 'Sebut 5+ bagian tubuh', 2],
            ['JD', 'JD-TA03', 'Cerita kegiatan kemarin sederhana', 3],
            ['JD', 'JD-TA04', 'Sebut 2-3 kesukaan diri', 4],

            // SE - TK A (4)
            ['SE', 'SE-TA01', 'Tunggu giliran 2-3 menit', 1],
            ['SE', 'SE-TA02', 'Bilang "senang/sedih" saat ditanya', 2],
            ['SE', 'SE-TA03', 'Main berpasangan (2 orang)', 3],
            ['SE', 'SE-TA04', 'Peluk teman yang sedih', 4],

            // FM - TK A (4)
            ['FM', 'FM-TA01', 'Lari/lompat tanpa jatuh sering', 1],
            ['FM', 'FM-TA02', 'Gunting garis lurus tebal', 2],
            ['FM', 'FM-TA03', 'Tulis 3-4 huruf nama sendiri', 3],
            ['FM', 'FM-TA04', 'Susun balok 4-5 tingkat', 4],

            // LIT - TK A (4)
            ['LIT', 'LIT-TA01', 'Kenali 10+ huruf besar', 1],
            ['LIT', 'LIT-TA02', 'Baca 5+ kata sederhana', 2],
            ['LIT', 'LIT-TA03', 'Tulis nama lengkap huruf besar', 3],
            ['LIT', 'LIT-TA04', 'Ikuti 3 langkah instruksi', 4],

            // MAT - TK A (4)
            ['MAT', 'MAT-TA01', 'Hitung sampai 10 tunjuk benda', 1],
            ['MAT', 'MAT-TA02', 'Pola sederhana ABAB', 2],
            ['MAT', 'MAT-TA03', 'Beda besar-kecil, tinggi-pendek', 3],
            ['MAT', 'MAT-TA04', 'Kenali lingkaran, persegi', 4],

            // SAI - TK A (3)
            ['SAI', 'SAI-TA01', 'Amati cuaca pagi/siang/sore', 1],
            ['SAI', 'SAI-TA02', 'Rawat tanaman siram/hangatkan', 2],
            ['SAI', 'SAI-TA03', 'Beda benda keras/lunak', 3],

            // SEN - TK A (3)
            ['SEN', 'SEN-TA01', 'Gambar bentuk sederhana (bulat, garis)', 1],
            ['SEN', 'SEN-TA02', 'Nyanyi lagu kelas lengkap', 2],
            ['SEN', 'SEN-TA03', 'Gerak ikut irama tepat', 3],
        ];
    }

    private function getTKBIndikator(): array
    {
        return [
            // NAG - TK B (5-6 thn) (5)
            ['NAG', 'NAG-01', 'Berdoa sebelum/sesudah makan & kegiatan sesuai agama', 1],
            ['NAG', 'NAG-02', 'Mengucap syukur atas nikmat Tuhan', 2],
            ['NAG', 'NAG-03', 'Peduli teman yang kesulitan/sedih', 3],
            ['NAG', 'NAG-04', 'Hargai perbedaan agama/budaya teman', 4],
            ['NAG', 'NAG-05', 'Jaga kebersihan lingkungan sekolah', 5],

            // BP - TK B (5)
            ['BP', 'BP-01', 'Pakai kata sopan lengkap dalam situasi berbeda', 1],
            ['BP', 'BP-02', 'Jujur: ngaku salah, kembalikan barang', 2],
            ['BP', 'BP-03', 'Berbagi tanpa diminta & inisiatif tolong', 3],
            ['BP', 'BP-04', 'Ikuti aturan kompleks (antri, giliran panjang)', 4],
            ['BP', 'BP-05', 'Pimpin teman dalam permainan sederhana', 5],

            // JD - TK B (5)
            ['JD', 'JD-01', 'Sebut nama lengkap, ortu, alamat detail', 1],
            ['JD', 'JD-02', 'Sebut semua bagian tubuh & fungsinya', 2],
            ['JD', 'JD-03', 'Perkenalkan diri lancar di depan kelas', 3],
            ['JD', 'JD-04', 'Cerita pengalaman pribadi runtut', 4],
            ['JD', 'JD-05', 'Punya cita-cita & jelaskan alasannya', 5],

            // SE - TK B (5)
            ['SE', 'SE-01', 'Tunggu giliran 5+ menit', 1],
            ['SE', 'SE-02', 'Kelola emosi konflik teman', 2],
            ['SE', 'SE-03', 'Kerja sama kelompok 4-5 orang', 3],
            ['SE', 'SE-04', 'Solusi konflik sederhana (bagi 2)', 4],
            ['SE', 'SE-05', 'Pimpin kelompok kecil', 5],

            // FM - TK B (5)
            ['FM', 'FM-01', 'Lari zig-zag, lompat jauh', 1],
            ['FM', 'FM-02', 'Gunting bentuk geometri sederhana', 2],
            ['FM', 'FM-03', 'Tulis nama lengkap huruf kecil', 3],
            ['FM', 'FM-04', 'Buka-tutup baju/zipper sendiri', 4],
            ['FM', 'FM-05', 'Lompat tali/kelereng sederhana', 5],

            // LIT - TK B (6)
            ['LIT', 'LIT-01', 'Kenali semua huruf besar-kecil', 1],
            ['LIT', 'LIT-02', 'Baca kalimat pendek 3-4 kata', 2],
            ['LIT', 'LIT-03', 'Tulis kalimat nama sekolah', 3],
            ['LIT', 'LIT-04', 'Cerita dari buku bergambar runtut', 4],
            ['LIT', 'LIT-05', 'Tanya jawab isi cerita kompleks', 5],
            ['LIT', 'LIT-06', 'Ikuti instruksi 4 langkah', 6],

            // MAT - TK B (6)
            ['MAT', 'MAT-01', 'Hitung sampai 20 & operasi sederhana', 1],
            ['MAT', 'MAT-02', 'Pola kompleks ABCABC', 2],
            ['MAT', 'MAT-03', 'Ukur panjang/datar pakai penggaris', 3],
            ['MAT', 'MAT-04', 'Kenali semua bentuk geometri dasar', 4],
            ['MAT', 'MAT-05', 'Baca jam analog jam penuh', 5],
            ['MAT', 'MAT-06', 'Pahami konsep uang sederhana', 6],

            // SAI - TK B (5)
            ['SAI', 'SAI-01', 'Ramal cuaca besok berdasarkan pola', 1],
            ['SAI', 'SAI-02', 'Eksperimen sederhana (air+garam)', 2],
            ['SAI', 'SAI-03', 'Tumbuh tanaman dari biji', 3],
            ['SAI', 'SAI-04', 'Beda magnet tarik/tempel', 4],
            ['SAI', 'SAI-05', 'Hipotesis sederhana (kalau...maka...)', 5],

            // TEK - TK B (4)
            ['TEK', 'TEK-01', 'Program sederhana tablet/komputer', 1],
            ['TEK', 'TEK-02', 'Rancang alat bantu sederhana', 2],
            ['TEK', 'TEK-03', 'Pakai alat ukur (penggaris, timbangan)', 3],
            ['TEK', 'TEK-04', 'Koding blok sederhana (move, turn)', 4],

            // SEN - TK B (5)
            ['SEN', 'SEN-01', 'Gambar narasi dengan detail', 1],
            ['SEN', 'SEN-02', 'Nyanyi polyphone sederhana', 2],
            ['SEN', 'SEN-03', 'Koreografi tari 8 hitungan', 3],
            ['SEN', 'SEN-04', 'Kritik seni teman secara sopan', 4],
            ['SEN', 'SEN-05', 'Main peran dengan dialog', 5],
        ];
    }

    private function insertIndikatorBatch(array $indikatorData, string $usia): void
    {
        foreach ($indikatorData as $data) {
            [$kodeDimensi, $kodeIndikator, $deskripsi, $urutan] = $data;

            $dimensiId = DB::table('dimensi_pembelajaran')->where('kode', $kodeDimensi)->value('id');

            if ($dimensiId) {
                DB::table('indikator_capaian')->insertOrIgnore([
                    'dimensi_id' => $dimensiId,
                    'kode_indikator' => $kodeIndikator,
                    'deskripsi' => $deskripsi,
                    'rentang_usia' => $usia,
                    'urutan' => $urutan,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function seedTemplateNarasi(): void
    {
        $kategori = ['BSB', 'BSH', 'MB', 'BB'];
        $dimensiIds = DB::table('dimensi_pembelajaran')->pluck('id', 'kode');

        foreach ($dimensiIds as $kode => $dimensiId) {
            foreach ($kategori as $kat) {
                DB::table('template_narasi')->updateOrInsert(
                    [
                        'dimensi_id' => $dimensiId,
                        'kategori_penilaian' => $kat
                    ],
                    [
                        'template_kalimat' => $this->getNarasiTemplate($kode, $kat),
                        'placeholder_options' => json_encode(['kata_puji' => ['baik', 'sangat baik', 'luar biasa']]),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    private function getDeskripsiDimensi(string $kode): string
    {
        return match ($kode) {
            'NAG' => 'Anak mengenal Tuhan, ibadah sederhana, syukur, peduli sesama & alam',
            'BP' => 'Anak jujur, disiplin, santun, toleran, bertanggung jawab',
            'JD' => 'Anak mengenal diri, keluarga, percaya diri, berpendapat',
            default => 'Dimensi pembelajaran PAUD lengkap (Playgroup-TK B)',
        };
    }

    private function getNarasiTemplate(string $kode, string $kategori): string
    {
        $templates = [
            'BSB' => '{nama_lengkap} berkembang SANGAT BAIK  dalam indikator {dimensi.deskripsi}',
            'BSH' => '{nama_lengkap} berkembang SESUAI HARAPAN  dalam indikator {dimensi.deskripsi}',
            'MB' => '{nama_lengkap} MULAI berkembang  dalam indikator {dimensi.deskripsi}',
            'BB' => '{nama_lengkap} BELUM berkembang  dalam indikator {dimensi.deskripsi}'
        ];

        return str_replace(['{dimensi}'], [$kode], $templates[$kategori]);
    }

    private function showSummaryByUsia(): void
    {
        $summary = DB::table('indikator_capaian')
            ->select('rentang_usia', DB::raw('count(*) as total'))
            ->groupBy('rentang_usia')
            ->orderBy('rentang_usia')
            ->get();

        $this->command->table(
            ['Rentang Usia', 'Indikator', 'Contoh'],
            [
                ['Playgroup (2-3 thn)', $summary->where('rentang_usia', '2-3')->first()->total ?? 0, 'Ikut berdoa (lipat tangan)'],
                ['TK A (4-5 thn)', $summary->where('rentang_usia', '4-5')->first()->total ?? 0, 'Berdoa sendiri + baca 5 kata'],
                ['TK B (5-6 thn)', $summary->where('rentang_usia', '5-6')->first()->total ?? 0, 'Hitung 20 + hipotesis sains'],
                ['TOTAL', DB::table('indikator_capaian')->count(), '100+ indikator lengkap']
            ]
        );
    }
}
