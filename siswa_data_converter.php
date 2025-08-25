<?php
/**
 * Script untuk mengonversi data siswa dari format lama ke format yang sesuai dengan sistem Filament School
 *
 * Cara penggunaan:
 * 1. Export data lama ke format CSV atau array PHP
 * 2. Sesuaikan mapping kolom di bawah sesuai dengan struktur data Anda
 * 3. Jalankan script untuk mendapatkan data yang sudah dikonversi
 */

// Contoh data input (ganti dengan data aktual Anda)
$oldData = [
    [
        'nomor_induk' => '20250001',
        'nisn' => '1234567890',
        'nama_siswa' => 'John Doe',
        'tempat_lahir' => 'Jakarta',
        'tgl_lahir' => '2005-01-01',
        'jk' => 'Laki-laki',
        'agama' => 'Islam',
        'nama_ayah' => 'Robert Doe',
        'nama_ibu' => 'Jane Doe',
        'no_telp' => '08123456789',
        'alamat' => 'Jl. Contoh No. 123'
    ],
    [
        'nomor_induk' => '20250002',
        'nisn' => '',
        'nama_siswa' => 'Jane Smith',
        'tempat_lahir' => 'Bandung',
        'tgl_lahir' => '2006-02-15',
        'jk' => 'Perempuan',
        'agama' => 'Islam',
        'nama_ayah' => 'Michael Smith',
        'nama_ibu' => 'Sarah Smith',
        'no_telp' => '',
        'alamat' => 'Jl. Test No. 456'
    ]
];

// Fungsi utama untuk konversi data
function convertSiswaData(array $oldData): array {
    $converted = [];

    foreach ($oldData as $row) {
        $newRow = [
            'NIS' => formatNIS($row['nomor_induk'] ?? ''),
            'NISN' => $row['nisn'] ?? '',
            'Nama Lengkap' => $row['nama_siswa'] ?? $row['nama'] ?? '',
            'Tempat Lahir' => $row['tempat_lahir'] ?? '',
            'Tanggal Lahir' => formatDate($row['tgl_lahir'] ?? ''),
            'Jenis Kelamin' => convertGender($row['jk'] ?? $row['gender'] ?? ''),
            'Agama' => validateReligion($row['agama'] ?? ''),
            'Nama Ayah' => $row['nama_ayah'] ?? $row['ayah'] ?? '',
            'Nama Ibu' => $row['nama_ibu'] ?? $row['ibu'] ?? '',
            'Telepon' => $row['no_telp'] ?? $row['telp'] ?? $row['telepon'] ?? '',
            'Alamat' => $row['alamat'] ?? ''
        ];

        $converted[] = $newRow;
    }

    return $converted;
}

// Format NIS sesuai dengan sistem (YYYY.XXXX)
function formatNIS(string $nomorInduk): string {
    $nomorInduk = trim($nomorInduk);

    // Jika sudah dalam format yang benar
    if (preg_match('/^\d{4}\.\d{4}$/', $nomorInduk)) {
        return $nomorInduk;
    }

    // Konversi dari format 8 digit (YYYYXXXX)
    if (preg_match('/^(\d{4})(\d{4})$/', $nomorInduk, $matches)) {
        $year = $matches[1];
        $sequence = $matches[2];
        $yearCode = substr($year, 2, 2) . substr($year + 1, 2, 2);
        return $yearCode . '.' . $sequence;
    }

    // Konversi dari format lain (sesuaikan dengan kebutuhan)
    // Contoh: jika nomor induk adalah tahun + urutan tanpa pemisah
    if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $nomorInduk, $matches)) {
        $yearPart1 = $matches[1];
        $yearPart2 = $matches[2];
        $sequence = $matches[3];
        return $yearPart1 . $yearPart2 . '.' . $sequence;
    }

    return $nomorInduk; // Kembalikan as-is jika tidak bisa dikonversi
}

// Format tanggal ke YYYY-MM-DD
function formatDate($date): string {
    if (empty($date)) {
        return '';
    }

    // Jika sudah dalam format YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }

    // Coba parsing berbagai format tanggal
    try {
        $formats = [
            'd/m/Y', 'd-m-Y', 'd F Y', 'd M Y', 'Y-m-d', 'Y/m/d',
            'm/d/Y', 'm-d-Y', 'F d, Y', 'M d, Y'
        ];

        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $date);
            if ($dateTime !== false) {
                return $dateTime->format('Y-m-d');
            }
        }

        // Coba parsing dengan strtotime sebagai fallback
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
    } catch (Exception $e) {
        // Tangani error parsing
    }

    return ''; // Kembalikan string kosong jika parsing gagal
}

// Konversi jenis kelamin ke format singkat
function convertGender(string $gender): string {
    $gender = strtolower(trim($gender));

    $malePatterns = ['l', 'laki-laki', 'lakilaki', 'male', 'pria', 'cowok', 'laki'];
    $femalePatterns = ['p', 'perempuan', 'female', 'wanita', 'cewek', 'perempuan'];

    foreach ($malePatterns as $pattern) {
        if (strpos($gender, $pattern) !== false) {
            return 'L';
        }
    }

    foreach ($femalePatterns as $pattern) {
        if (strpos($gender, $pattern) !== false) {
            return 'P';
        }
    }

    return ''; // Kembalikan string kosong jika tidak dikenali
}

// Validasi dan standarisasi agama
function validateReligion(string $religion): string {
    $religion = trim($religion);
    if (empty($religion)) {
        return '';
    }

    $validReligions = [
        'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'
    ];

    // Cek apakah sudah valid
    if (in_array($religion, $validReligions)) {
        return $religion;
    }

    // Mapping agama yang mungkin berbeda
    $mapping = [
        'islam' => 'Islam',
        'muslim' => 'Islam',
        'muhammad' => 'Islam',
        'kristen' => 'Kristen',
        'protestan' => 'Kristen',
        'christian' => 'Kristen',
        'katolik' => 'Katolik',
        'catholic' => 'Katolik',
        'hindu' => 'Hindu',
        'hindhu' => 'Hindu',
        'budha' => 'Buddha',
        'buddha' => 'Buddha',
        'buddhist' => 'Buddha',
        'konghucu' => 'Konghucu',
        'confucius' => 'Konghucu',
        'confucianism' => 'Konghucu'
    ];

    $religionLower = strtolower($religion);
    foreach ($mapping as $key => $value) {
        if (strpos($religionLower, $key) !== false) {
            return $value;
        }
    }

    return ''; // Kembalikan string kosong jika tidak valid
}

// Fungsi untuk mengekspor ke CSV
function exportToCSV(array $data, string $filename = 'siswa_converted.csv'): void {
    if (empty($data)) {
        echo "Tidak ada data untuk diekspor.\n";
        return;
    }

    $file = fopen($filename, 'w');

    // Tulis header
    fputcsv($file, array_keys($data[0]));

    // Tulis data
    foreach ($data as $row) {
        fputcsv($file, $row);
    }

    fclose($file);
    echo "Data berhasil diekspor ke: $filename\n";
}

// Fungsi untuk menampilkan data dalam format tabel
function displayDataAsTable(array $data): void {
    if (empty($data)) {
        echo "Tidak ada data untuk ditampilkan.\n";
        return;
    }

    $headers = array_keys($data[0]);
    $maxLengths = array_fill_keys($headers, 0);

    // Hitung panjang maksimum untuk setiap kolom
    foreach ($data as $row) {
        foreach ($row as $key => $value) {
            $maxLengths[$key] = max($maxLengths[$key], strlen($value));
        }
    }

    // Tampilkan header
    echo "\n";
    foreach ($headers as $header) {
        printf("%-" . ($maxLengths[$header] + 2) . "s", $header);
    }
    echo "\n";

    // Tampilkan separator
    foreach ($headers as $header) {
        echo str_repeat('-', $maxLengths[$header] + 2);
    }
    echo "\n";

    // Tampilkan data
    foreach ($data as $row) {
        foreach ($row as $key => $value) {
            printf("%-" . ($maxLengths[$key] + 2) . "s", $value);
        }
        echo "\n";
    }
    echo "\n";
}

// Main execution
echo "=== Konversi Data Siswa ===\n";
echo "Memproses " . count($oldData) . " record...\n";

$convertedData = convertSiswaData($oldData);

echo "Konversi selesai. Hasil:\n";
displayDataAsTable($convertedData);

// Ekspor ke CSV
exportToCSV($convertedData);

echo "\nInstruksi penggunaan:\n";
echo "1. Ganti variabel \$oldData dengan data aktual Anda\n";
echo "2. Sesuaikan mapping kolom dalam fungsi convertSiswaData()\n";
echo "3. Jalankan script: php siswa_data_converter.php\n";
echo "4. File CSV akan dibuat: siswa_converted.csv\n";
echo "5. Import file CSV tersebut ke sistem Filament School\n";

// Contoh output untuk preview
echo "\nPreview data yang dikonversi:\n";
print_r($convertedData);
?>
