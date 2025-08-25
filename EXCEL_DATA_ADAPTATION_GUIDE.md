# Panduan Adaptasi Data Excel untuk Import Siswa

Berdasarkan analisis sistem Filament School, berikut adalah panduan lengkap untuk mengadaptasi data Excel Anda agar sesuai dengan format yang diharapkan untuk import data siswa.

## Struktur Kolom yang Diharapkan

Sistem mengharapkan kolom-kolom berikut dalam file Excel:

| Nama Kolom (Header) | Tipe Data | Wajib? | Keterangan |
|---------------------|-----------|--------|------------|
| **NIS** | String (9 karakter) | ✅ | Format: YYYY.XXXX (contoh: 2526.0001) |
| **NISN** | String (10 karakter) | ❌ | Nomor Induk Siswa Nasional |
| **Nama Lengkap** | String (255 karakter) | ✅ | Nama lengkap siswa |
| **Tempat Lahir** | String (255 karakter) | ❌ | Kota/kabupaten tempat lahir |
| **Tanggal Lahir** | Date | ❌ | Format: YYYY-MM-DD |
| **Jenis Kelamin** | String (1 karakter) | ❌ | 'L' untuk Laki-laki, 'P' untuk Perempuan |
| **Agama** | String (50 karakter) | ❌ | Islam, Kristen, Katolik, Hindu, Buddha, Konghucu |
| **Nama Ayah** | String (255 karakter) | ❌ | Nama ayah siswa |
| **Nama Ibu** | String (255 karakter) | ❌ | Nama ibu siswa |
| **Telepon** | String (16 karakter) | ❌ | Nomor telepon |
| **Alamat** | String (255 karakter) | ❌ | Alamat lengkap |
| **provinsi_id** | Integer | ❌ | ID provinsi (jika menggunakan sistem wilayah) |
| **kota_id** | Integer | ❌ | ID kota/kabupaten |
| **kecamatan_id** | Integer | ❌ | ID kecamatan |
| **kelurahan_id** | Integer | ❌ | ID kelurahan/desa |

## Langkah-langkah Adaptasi Data

### 1. Pastikan Format NIS Sesuai
- Format NIS harus: `YYYY.XXXX` (4 digit tahun + titik + 4 digit urutan)
- Contoh: `2526.0001` (Tahun ajaran 2025/2026, urutan 0001)

### 2. Konversi Jenis Kelamin
- Ubah data jenis kelamin menjadi format singkat:
  - "Laki-laki" → "L"
  - "Perempuan" → "P"

### 3. Format Tanggal Lahir
- Pastikan tanggal lahir dalam format: `YYYY-MM-DD`
- Contoh: `2005-01-15`

### 4. Validasi Agama
- Pastikan agama menggunakan nilai yang valid:
  - Islam, Kristen, Katolik, Hindu, Buddha, Konghucu

### 5. Template Excel yang Disarankan

```csv
NIS,NISN,Nama Lengkap,Tempat Lahir,Tanggal Lahir,Jenis Kelamin,Agama,Nama Ayah,Nama Ibu,Telepon,Alamat
2526.0001,1234567890,John Doe,Jakarta,2005-01-01,L,Islam,Robert Doe,Jane Doe,08123456789,Jl. Contoh No. 123
2526.0002,,Jane Smith,Bandung,2006-02-15,P,Islam,Michael Smith,Sarah Smith,,Jl. Test No. 456
```

## Script PHP untuk Konversi Data

Jika Anda memiliki data dalam format yang berbeda, berikut adalah script PHP untuk membantu konversi:

```php
<?php
// Contoh script untuk mengonversi data dari format lama ke format baru
function convertSiswaData($oldData) {
    $converted = [];
    
    foreach ($oldData as $row) {
        $newRow = [
            'NIS' => formatNIS($row['nomor_induk'] ?? ''),
            'NISN' => $row['nisn'] ?? '',
            'Nama Lengkap' => $row['nama'] ?? $row['nama_siswa'] ?? '',
            'Tempat Lahir' => $row['tempat_lahir'] ?? '',
            'Tanggal Lahir' => formatDate($row['tgl_lahir'] ?? ''),
            'Jenis Kelamin' => convertGender($row['jk'] ?? $row['gender'] ?? ''),
            'Agama' => validateReligion($row['agama'] ?? ''),
            'Nama Ayah' => $row['ayah'] ?? $row['nama_ayah'] ?? '',
            'Nama Ibu' => $row['ibu'] ?? $row['nama_ibu'] ?? '',
            'Telepon' => $row['telp'] ?? $row['no_telp'] ?? '',
            'Alamat' => $row['alamat'] ?? ''
        ];
        
        $converted[] = $newRow;
    }
    
    return $converted;
}

function formatNIS($nomorInduk) {
    // Logika untuk mengonversi nomor induk ke format NIS
    // Contoh: jika nomor induk adalah "20250001", kembalikan "2526.0001"
    if (preg_match('/^(\d{4})(\d{4})$/', $nomorInduk, $matches)) {
        $year = $matches[1];
        $sequence = $matches[2];
        $yearCode = substr($year, 2, 2) . substr($year + 1, 2, 2);
        return $yearCode . '.' . $sequence;
    }
    return $nomorInduk;
}

function formatDate($date) {
    // Konversi berbagai format tanggal ke YYYY-MM-DD
    if ($date instanceof DateTime) {
        return $date->format('Y-m-d');
    }
    
    if (is_numeric($date)) {
        // Jika tanggal dalam format Excel (serial number)
        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
    }
    
    // Coba parsing berbagai format tanggal
    try {
        return (new DateTime($date))->format('Y-m-d');
    } catch (Exception $e) {
        return '';
    }
}

function convertGender($gender) {
    $gender = strtolower(trim($gender));
    if (in_array($gender, ['l', 'laki-laki', 'male', 'pria'])) {
        return 'L';
    }
    if (in_array($gender, ['p', 'perempuan', 'female', 'wanita'])) {
        return 'P';
    }
    return '';
}

function validateReligion($religion) {
    $validReligions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
    $religion = ucfirst(trim($religion));
    
    if (in_array($religion, $validReligions)) {
        return $religion;
    }
    
    // Mapping agama yang mungkin berbeda
    $mapping = [
        'Islam' => 'Islam',
        'Muslim' => 'Islam',
        'Kristen Protestan' => 'Kristen',
        'Protestan' => 'Kristen',
        'Katolik' => 'Katolik',
        'Hindu' => 'Hindu',
        'Budha' => 'Buddha',
        'Buddha' => 'Buddha',
        'Konghucu' => 'Konghucu',
        'Confucianism' => 'Konghucu'
    ];
    
    return $mapping[$religion] ?? '';
}
```

## Tips Import

1. **Backup Data**: Selalu backup database sebelum melakukan import
2. **Test dengan Data Sample**: Import data sample kecil terlebih dahulu
3. **Periksa Error**: Sistem akan melewatkan baris yang error dan melanjutkan import
4. **Validasi Manual**: Periksa beberapa record setelah import untuk memastikan data sesuai

## Troubleshooting

### Error Umum:
- **NIS sudah ada**: Sistem menggunakan NIS sebagai identifier unik
- **Format tanggal tidak valid**: Pastikan format YYYY-MM-DD
- **Jenis kelamin tidak valid**: Hanya 'L' atau 'P' yang diterima

### Solusi:
- Untuk data duplikat, sistem akan mengupdate data yang sudah ada
- Gunakan fungsi `formatDate()` untuk mengonversi format tanggal
- Gunakan fungsi `convertGender()` untuk menstandarkan jenis kelamin

## Download Template Resmi

Anda dapat mendownload template resmi dari sistem melalui:
1. Buka menu "Manajemen Siswa"
2. Klik tombol "Download Template Excel"
3. Gunakan template tersebut sebagai referensi format

Dengan mengikuti panduan ini, data Excel Anda akan siap untuk diimport ke sistem Filament School.
