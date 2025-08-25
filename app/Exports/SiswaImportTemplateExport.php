<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Laravolt\Indonesia\Models\Province;

class SiswaImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    use Exportable;

    public function array(): array
    {
        // Fetch all provinces as enum data
        // $provinces = Province::pluck('name')->toArray();

        // Sample data row with enum names for location fields
        // Here we just use the first province as sample, you can adjust as needed
        return [
            [
                '2526.0001',       // NIS
                '1234567890',      // NISN
                'John Doe',        // Nama Lengkap
                'Jakarta',         // Tempat Lahir
                '2005-01-01',      // Tanggal Lahir
                'L',               // Jenis Kelamin
                'Islam',           // Agama
                'Robert Doe',      // Nama Ayah
                'Jane Doe',        // Nama Ibu
                '08123456789',     // Telepon

            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Nama Ayah',
            'Nama Ibu',
            'Telepon',

        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Siswa Import Template';
    }
}
