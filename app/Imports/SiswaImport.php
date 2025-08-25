<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnErrors;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Update or create siswa by nis (unique)
        return Siswa::updateOrCreate(
            ['nis' => $row['nis']],
            [
                'nisn' => $row['nisn'] ?? null,
                'nama_lengkap' => $row['nama_lengkap'] ?? null,
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => isset($row['tanggal_lahir']) ? \Carbon\Carbon::parse($row['tanggal_lahir']) : null,
                'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                'agama' => $row['agama'] ?? null,
                'nama_ayah' => $row['nama_ayah'] ?? null,
                'nama_ibu' => $row['nama_ibu'] ?? null,
                'telepon' => $row['telepon'] ?? null,
                'alamat' => $row['alamat'] ?? null,
                'provinsi_id' => $row['provinsi_id'] ?? null,
                'kota_id' => $row['kota_id'] ?? null,
                'kecamatan_id' => $row['kecamatan_id'] ?? null,
                'kelurahan_id' => $row['kelurahan_id'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:9'],
            'nisn' => ['nullable', 'string', 'max:10'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:50'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:16'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'provinsi_id' => ['nullable', 'integer'],
            'kota_id' => ['nullable', 'integer'],
            'kecamatan_id' => ['nullable', 'integer'],
            'kelurahan_id' => ['nullable', 'integer'],
        ];
    }
}
