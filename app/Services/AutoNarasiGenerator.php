<?php

namespace App\Services;

use App\Models\{ObservasiHarian, PenilaianSemester, TemplateNarasi, DimensiPembelajaran};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoNarasiGenerator
{
    /**
     * Generate narasi penilaian untuk semua dimensi seorang siswa
     */
    public function generatePenilaianSemester($siswa_id, $periodeId)
    {
        $periode = $this->getPeriodeSemester($periodeId);
        $dimensi_list = DimensiPembelajaran::where('is_active', true)->orderBy('urutan')->get();

        $hasil = [];

        foreach ($dimensi_list as $dimensi) {
            $narasi = $this->generateNarasiDimensi($siswa_id, $dimensi->id, $periode['start'], $periode['end']);

            if ($narasi) {
                $penilaian = PenilaianSemester::updateOrCreate(
                    [
                        'siswa_id' => $siswa_id,
                        'periode_id' => $periodeId,
                        'dimensi_id' => $dimensi->id
                    ],
                    [
                        'kategori_akhir' => $narasi['kategori'],
                        'narasi_auto' => $narasi['narasi']
                    ]
                );

                $hasil[] = $penilaian;
            }
        }

        return $hasil;
    }

    /**
     * Generate narasi untuk satu dimensi spesifik
     */
    private function generateNarasiDimensi($siswa_id, $dimensi_id, $tanggal_mulai, $tanggal_akhir)
    {
        // Ambil semua observasi untuk dimensi ini
        $observasi = ObservasiHarian::where('siswa_id', $siswa_id)
            ->whereBetween('tanggal_observasi', [$tanggal_mulai, $tanggal_akhir])
            ->whereHas('indikator', function($q) use ($dimensi_id) {
                $q->where('dimensi_id', $dimensi_id);
            })
            ->with(['indikator', 'siswa'])
            ->get();

        if ($observasi->isEmpty()) {
            return null;
        }

        // Hitung frekuensi kategori
        $frekuensi = [
            'BSB' => 0,
            'BSH' => 0,
            'MB' => 0,
            'BB' => 0
        ];

        foreach ($observasi as $obs) {
            $frekuensi[$obs->kategori_penilaian]++;
        }

        // Tentukan kategori akhir (yang paling sering muncul)
        arsort($frekuensi);
        $kategori_akhir = array_key_first($frekuensi);

        // Ambil template narasi
        $template = TemplateNarasi::where('dimensi_id', $dimensi_id)
            ->where('kategori_penilaian', $kategori_akhir)
            ->first();

        if (!$template) {
            return [
                'kategori' => $kategori_akhir,
                'narasi' => $this->generateNarasiDefault($observasi->first()->siswa, $kategori_akhir)
            ];
        }

        // Generate narasi dari template
        $narasi = $this->parseTemplate($template, $observasi);

        // Tambahkan contoh konkret dari catatan guru
        $contoh = $this->getContohTerbaik($observasi, $kategori_akhir);
        if ($contoh) {
            $narasi .= " Contoh: " . $contoh;
        }

        return [
            'kategori' => $kategori_akhir,
            'narasi' => $narasi
        ];
    }

    /**
     * Parse template dengan placeholder
     */
    private function parseTemplate($template, $observasi)
    {
        $siswa = $observasi->first()->siswa;
        $narasi = $template->template_kalimat;

        // Replace placeholder dasar
        $narasi = str_replace('{nama}', $siswa->nama, $narasi);
        $narasi = str_replace('{nama_panggilan}', $siswa->nama_panggilan ?? $siswa->nama, $narasi);

        // Replace placeholder dari options
        if ($template->placeholder_options) {
            foreach ($template->placeholder_options as $key => $values) {
                if (is_array($values) && !empty($values)) {
                    $narasi = str_replace('{'.$key.'}', $values[array_rand($values)], $narasi);
                }
            }
        }

        return $narasi;
    }

    /**
     * Ambil contoh terbaik dari catatan guru
     */
    private function getContohTerbaik($observasi, $kategori)
    {
        $obs_terbaik = $observasi
            ->where('kategori_penilaian', $kategori)
            ->whereNotNull('catatan_guru')
            ->sortByDesc('tanggal_observasi')
            ->first();

        return $obs_terbaik ? $obs_terbaik->catatan_guru : null;
    }

    /**
     * Generate narasi default jika template tidak ada
     */
    private function generateNarasiDefault($siswa, $kategori)
    {
        $nama = $siswa->nama_panggilan ?? $siswa->nama;

        $templates_default = [
            'BSB' => "$nama menunjukkan pencapaian yang sangat baik dalam aspek ini.",
            'BSH' => "$nama telah mencapai perkembangan sesuai harapan.",
            'MB' => "$nama mulai berkembang dalam aspek ini.",
            'BB' => "$nama masih memerlukan bimbingan lebih lanjut."
        ];

        return $templates_default[$kategori] ?? "$nama menunjukkan perkembangan.";
    }

    /**
     * Hitung periode tanggal untuk semester
     */
    private function getPeriodeSemester($periodeId)
    {
        $tahun = (int) substr($periodeId, 0, 4);

        if ($periodeId === 'ganjil') {
            return [
                'start' => Carbon::create($tahun, 7, 1),
                'end' => Carbon::create($tahun, 12, 31)
            ];
        } else {
            return [
                'start' => Carbon::create($tahun + 1, 1, 1),
                'end' => Carbon::create($tahun + 1, 6, 30)
            ];
        }
    }

    /**
     * Generate statistik perkembangan siswa
     */
    public function getStatistikPerkembangan($siswa_id, $periodeId)
    {
        $periode = $this->getPeriodeSemester($periodeId);

        $stats = ObservasiHarian::where('siswa_id', $siswa_id)
            ->whereBetween('tanggal_observasi', [$periode['start'], $periode['end']])
            ->select('kategori_penilaian', DB::raw('count(*) as total'))
            ->groupBy('kategori_penilaian')
            ->pluck('total', 'kategori_penilaian')
            ->toArray();

        $total = array_sum($stats);

        return [
            'total_observasi' => $total,
            'distribusi' => $stats,
            'persentase' => array_map(fn($val) => round($val / $total * 100, 1), $stats)
        ];
    }
}