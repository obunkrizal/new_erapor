<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{DimensiPembelajaran, IndikatorCapaian, TemplateNarasi};

class PenilaianPAUDSeeder extends Seeder
{
    public function run()
    {
        // Seed Dimensi Pembelajaran
        $dimensi_data = [
            [
                'kode' => 'LIT',
                'nama' => 'Literasi',
                'kategori' => 'dasar_literasi_matematika_sains',
                'deskripsi' => 'Kemampuan dasar literasi PAUD',
                'urutan' => 1
            ],
            [
                'kode' => 'MAT',
                'nama' => 'Matematika',
                'kategori' => 'dasar_literasi_matematika_sains',
                'deskripsi' => 'Kemampuan dasar matematika PAUD',
                'urutan' => 2
            ],
            [
                'kode' => 'SAI',
                'nama' => 'Sains',
                'kategori' => 'dasar_literasi_matematika_sains',
                'deskripsi' => 'Kemampuan dasar sains PAUD',
                'urutan' => 3
            ],
            [
                'kode' => 'JD',
                'nama' => 'Identitas Diri',
                'kategori' => 'jati_diri',
                'deskripsi' => 'Pengenalan identitas diri anak',
                'urutan' => 4
            ],
            [
                'kode' => 'SE',
                'nama' => 'Sosial Emosional',
                'kategori' => 'jati_diri',
                'deskripsi' => 'Perkembangan sosial emosional',
                'urutan' => 5
            ],
            [
                'kode' => 'FM',
                'nama' => 'Fisik Motorik',
                'kategori' => 'jati_diri',
                'deskripsi' => 'Perkembangan fisik motorik',
                'urutan' => 6
            ],
            [
                'kode' => 'NAG',
                'nama' => 'Nilai Agama',
                'kategori' => 'nilai_agama_budi_pekerti',
                'deskripsi' => 'Nilai-nilai agama dan kepercayaan',
                'urutan' => 7
            ],
            [
                'kode' => 'BP',
                'nama' => 'Budi Pekerti',
                'kategori' => 'nilai_agama_budi_pekerti',
                'deskripsi' => 'Karakter dan budi pekerti',
                'urutan' => 8
            ]
        ];

        foreach ($dimensi_data as $data) {
            DimensiPembelajaran::create($data);
        }

        // Seed Template Narasi untuk Literasi
        $literasi_id = DimensiPembelajaran::where('kode', 'LIT')->first()->id;

        $templates_literasi = [
            [
                'dimensi_id' => $literasi_id,
                'kategori_penilaian' => 'BSB',
                'template_kalimat' => '{nama_lengkap} dapat mengenal dan menulis huruf serta membaca kata sederhana dengan sangat baik.',
                'placeholder_options' => json_encode([
                    'kemampuan' => ['mengenal huruf vokal dan konsonan', 'menulis nama sendiri', 'membaca kata sederhana'],
                    'kualitas' => ['dengan sangat baik', 'dengan mandiri', 'dengan antusias']
                ])
            ],
            [
                'dimensi_id' => $literasi_id,
                'kategori_penilaian' => 'BSH',
                'template_kalimat' => '{nama_lengkap} dapat mengenal huruf dan mulai belajar menulis dengan baik.',
                'placeholder_options' => null
            ],
            [
                'dimensi_id' => $literasi_id,
                'kategori_penilaian' => 'MB',
                'template_kalimat' => '{nama_lengkap} mulai mengenal beberapa huruf sederhana dan perlu pendampingan dalam menulis.',
                'placeholder_options' => null
            ],
            [
                'dimensi_id' => $literasi_id,
                'kategori_penilaian' => 'BB',
                'template_kalimat' => '{nama_lengkap} belum mampu mengenal huruf secara konsisten dan memerlukan bimbingan intensif.',
                'placeholder_options' => null
            ]
        ];

        foreach ($templates_literasi as $template) {
            TemplateNarasi::create($template);
        }

        // Seed Template Narasi untuk Nilai Agama
        $agama_id = DimensiPembelajaran::where('kode', 'NAG')->first()->id;

        $templates_agama = [
            [
                'dimensi_id' => $agama_id,
                'kategori_penilaian' => 'BSB',
                'template_kalimat' => '{nama_lengkap} dapat menghafal doa harian dan melaksanakan ibadah sederhana dengan baik.',
                'placeholder_options' => json_encode([
                    'ibadah' => ['doa makan', 'doa tidur', 'doa masuk kamar mandi'],
                    'sikap' => ['dengan khusyuk', 'dengan tertib', 'dengan mandiri']
                ])
            ],
            [
                'dimensi_id' => $agama_id,
                'kategori_penilaian' => 'BSH',
                'template_kalimat' => '{nama_lengkap} dapat mengikuti kegiatan ibadah dengan baik.',
                'placeholder_options' => null
            ],
            [
                'dimensi_id' => $agama_id,
                'kategori_penilaian' => 'MB',
                'template_kalimat' => '{nama_lengkap} mulai belajar mengenal praktik ibadah sederhana.',
                'placeholder_options' => null
            ],
            [
                'dimensi_id' => $agama_id,
                'kategori_penilaian' => 'BB',
                'template_kalimat' => '{nama_lengkap} belum dapat mengikuti kegiatan ibadah secara mandiri.',
                'placeholder_options' => null
            ]
        ];

        foreach ($templates_agama as $template) {
            TemplateNarasi::create($template);
        }
    }
}
