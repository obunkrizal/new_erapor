<!DOCTYPE html>
<html lang="id">
@php
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport- {{ $nilai->siswa->nama_lengkap }}</title>
    <!-- Main CSS File -->
    <link href="{{ asset('css/filament/print/printpage.css') }}" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
        }
    </style>
</head>

<body>


    @if (session('errorMessage') || $errors->has('validation') || isset($errorMessage))
        <div class="error-message-box">
            <p class="error-message-text">{{ $errorMessage }}</p>
            <button class="error-close-button" onclick="window.close()">Close</button>
        </div>
        <style>
            .error-message-box {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 25px 30px;
                margin: 30px auto;
                border-radius: 12px;
                max-width: 450px;
                text-align: center;
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                box-shadow: 0 8px 20px rgba(118, 75, 162, 0.4);
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 9999;
            }
            .error-message-text {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 20px;
                letter-spacing: 0.5px;
            }
            .error-close-button {
                background: rgba(255, 255, 255, 0.9);
                color: #764ba2;
                border: none;
                padding: 12px 28px;
                border-radius: 30px;
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
                outline: none;
            }
            .error-close-button:hover {
                background: #fff;
                color: #5a2d82;
                box-shadow: 0 6px 16px rgba(90, 45, 130, 0.5);
                transform: translateY(-2px);
            }
            .print-button {
                display: none !important;
            }
            .container {
                display: none !important;
            }
        </style>
    @endif
        <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>

        <div class="container">
            <!-- Header -->
            <div class="header">
            <div class="header-left">
                @if ($sekolah->logo)
                    <img src="{{ Storage::disk('public')->url($sekolah->logo) }}"
                        alt="Logo {{ $sekolah->nama_sekolah }}" class="header-logo">
                @endif
                <div class="header-info">
                    <h4>{{ Str::upper($sekolah->nama_sekolah) }}</h4>
                    <p>{{ $sekolah->alamat }}</p>
                    <p>Telp: {{ $sekolah->no_telp }} | Email: {{ $sekolah->email }}</p>
                    @if ($sekolah->npsn)
                        <p>NPSN: {{ $sekolah->npsn }}</p>
                    @endif
                </div>
            </div>
            <div class="header-right" style="text-align: right">
                <h1>Penilaian Hasil Belajar</h1>
                <h2>Sistem Informasi Akademik {{ $sekolah->nama_sekolah }} <br>TA: {{ $nilai->periode->tahun_ajaran }}
                </h2>
            </div>
        </div>

        <!-- Student Info Header with Photo -->
        <div class="student-info-header">
            <div class="student-basic-info">
                <div class="section-title">Data Pribadi</div>
                <div class="three-column">
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama Lengkap</td>
                                <td class="value"><strong>{{ Str::upper($nilai->siswa->nama_lengkap) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">NIS / NISN</td>
                                <td class="value"><strong>{{ $nilai->siswa->nis ?? '-' }} /
                                        {{ $nilai->siswa->nisn ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Semester</td>
                                <td class="value">{{ ucwords($nilai->periode->semester) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tahun Ajaran</td>
                                <td class="value">{{ $nilai->periode->tahun_ajaran }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Rombel</td>
                                <td class="value">Rombel {{ $nilai->kelas->nama_kelas }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tingkat</td>
                                <td class="value">Pondasi</td>
                            </tr>
                            <tr>
                                <td class="label">Berat Badan</td>
                                <td class="value">{{ $datamedis?->berat_badan ?? '-' }} KG</td>
                            </tr>
                            <tr>
                                <td class="label">Tingi Badan</td>
                                <td class="value">{{ $datamedis?->tinggi_badan ?? '-' }} CM</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <div>
                            <p style="text-align: center">Qr Code</p>

                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(STR::upper($nilai->siswa?->nama_lengkap) . '_ NIS: ' . $nilai->siswa?->nis . '_ NISN: ' . $nilai->siswa?->nisn . '_' . $nilai->siswa?->tempat_lahir . ', ' . Carbon\Carbon::parse($nilai->siswa?->tanggal_lahir)->translatedFormat('d F Y'), 'QRCODE', 3, 3) }}"
                                alt="barcode" width="65" />

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <h1 style="font-size:10pt;text-align:center;margin-bottom:1px">PENILAIAN HASIL BELAJAR PESERTA DIDIK</h1>
        <!-- Combined Information Sections -->
        <div class="section">
            <div class="section-title">Nilai Agama dan Budi Pekerti</div>
            <table class="info-table">
                <tr style="height: 100px;">
                    <td class="value" style="justify-content: space-between;column-span:2">
                        {{ $nilai->nilai_agama ?? 'Belum Ada Nilai' }}</td>
                </tr>

            </table>
        </div>

        <div class="section">
            <div class="section-title">Dokumentasi Nilai Agama dan Budi Pekerti</div>
            <table class="info-table">
                <tr style="height: 100px;">
                    <td class="value" style="justify-content: space-between;column-span:2">
                        <div style="display: flex; justify-content: center; gap: 16px;">
                            @if (isset($nilai->fotoAgama) && is_array($nilai->fotoAgama) && count($nilai->fotoAgama) > 0)
                                @foreach ($nilai->fotoAgama as $foto)
                                    <div>
                                        <img src="{{ Storage::url($foto) }}" alt="Foto Agama"
                                            style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 5px;">
                                    </div>
                                @endforeach
                            @elseif(isset($nilai->fotoAgama) && $nilai->fotoAgama)
                                <div>
                                    <img src="{{ Storage::url($nilai->fotoAgama) }}" alt="Foto Agama"
                                        style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                </div>
                            @else
                                <div
                                    style="border: 2px dashed #ccc; width: 100px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                    <span style="color: #999;">Tidak ada foto</span>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

            </table>
        </div>

        <!-- Combined Information Sections -->
        <div class="section">
            <div class="section-title">Nilai Jati Diri</div>
            <table class="info-table">
                <tr style="height: 100px;">
                    <td class="value" style="justify-content: space-between;column-span:2">
                        {{ $nilai->nilai_jatiDiri ?? 'Belum Ada Nilai' }}</td>
                </tr>

            </table>
        </div>

        <div class="section">
            <div class="section-title">Dokumentasi Nilai Jati Diri</div>
            <table class="info-table">
                <tr style="height: 100px;">
                    <td class="value" style="justify-content: space-between;column-span:2">
                        <div style="display: flex; justify-content: center; gap: 16px;">
                            @if (isset($nilai->fotoJatiDiri) && is_array($nilai->fotoJatiDiri) && count($nilai->fotoJatiDiri) > 0)
                                @foreach ($nilai->fotoJatiDiri as $foto)
                                    <div>
                                        <img src="{{ Storage::url($foto) }}" alt="Foto Agama"
                                            style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 5px;">
                                    </div>
                                @endforeach
                            @elseif(isset($nilai->fotoJatiDiri) && $nilai->fotoJatiDiri)
                                <div>
                                    <img src="{{ Storage::url($nilai->fotoJatiDiri) }}" alt="Foto Agama"
                                        style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                </div>
                            @else
                                <div
                                    style="border: 2px dashed #ccc; width: 100px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                    <span style="color: #999;">Belum ada foto</span>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>

            </table>
        </div>
        <!-- Combined Information Sections -->
        <div style="page-break-before: always; margin-top: 100px; padding-top: 70px;">
            <div class="section">
                <div class="section-title">Nilai Dasar-Dasar Literasi, Matematika, Sains, Rekayasa, Teknologi, dan Seni
                </div>
                <table class="info-table">
                    <tr style="height: 100px;">
                        <td class="value" style="justify-content: space-between;column-span:2">
                            {{ $nilai->nilai_literasi ?? 'Belum Ada Nilai' }}</td>
                    </tr>

                </table>
            </div>

            <div class="section">
                <div class="section-title">Dokumentasi Nilai Dasar-Dasar Literasi, Matematika, Sains, Rekayasa,
                    Teknologi, dan Seni</div>
                <table class="info-table">
                    <tr style="height: 100px;">
                        <td class="value" style="justify-content: space-between;column-span:2">
                            <div style="display: flex; justify-content: center; gap: 16px;">
                                @if (isset($nilai->fotoLiterasi) && is_array($nilai->fotoLiterasi) && count($nilai->fotoLiterasi) > 0)
                                    @foreach ($nilai->fotoLiterasi as $foto)
                                        <div>
                                            <img src="{{ Storage::url($foto) }}" alt="Foto Agama"
                                                style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 5px;">
                                        </div>
                                    @endforeach
                                @elseif(isset($nilai->fotoLiterasi) && $nilai->fotoLiterasi)
                                    <div>
                                        <img src="{{ Storage::url($nilai->fotoLiterasi) }}" alt="Foto Agama"
                                            style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                @else
                                    <div
                                        style="border: 2px dashed #ccc; width: 100px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                        <span style="color: #999;">Belum ada foto</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>

                </table>
            </div>
            <!-- Combined Information Sections -->
            <div class="section">
                <div class="section-title">Nilai Narasi Pembelajaran</div>
                <table class="info-table">
                    <tr style="height: 100px;">
                        <td class="value" style="justify-content: space-between;column-span:2">
                           {{ $nilai->nilai_narasi ?? 'Belum Ada Nilai' }}</td>
                    </tr>

                </table>
            </div>

            <div class="section">
                <div class="section-title">Dokumentasi Nilai Narasi</div>
                <table class="info-table">
                    <tr style="height: 100px;">
                        <td class="value" style="justify-content: space-between;column-span:2">
                            <div style="display: flex; justify-content: center; gap: 16px;">
                                @if (isset($nilai->fotoNarasi) && is_array($nilai->fotoNarasi) && count($nilai->fotoNarasi) > 0)
                                    @foreach ($nilai->fotoNarasi as $foto)
                                        <div>
                                            <img src="{{ Storage::url($foto) }}" alt="Foto Agama"
                                                style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 5px;">
                                        </div>
                                    @endforeach
                                @elseif(isset($nilai->fotoNarasi) && $nilai->fotoNarasi)
                                    <div>
                                        <img src="{{ Storage::url($nilai->fotoNarasi) }}" alt="Foto Agama"
                                            style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                @else
                                    <div
                                        style="border: 2px dashed #ccc; width: 100px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                        <span style="color: #999;">Belum ada foto</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>

                </table>
            </div>
        </div>
        <!-- Combined Information Sections -->
        <div style="page-break-before: always; margin-top: 100px; padding-top: 70px;">
            <div class="section">
                <div class="section-title">Refleksi Orang Tua/ Wali Murid</div>
                <table class="info-table">
                    <tr style="border-top: 2px solid #000;">
                        <td height="100" colspan="4" scope="col"
                            style="padding: 15px; vertical-align: top;font-size: 10pt">
                            <span>1. Apa yang sudah berkembang pada diri anak saya?</span>
                        </td>
                    </tr>
                    <tr style="border-top: 2px solid #000;">
                        <td height="100" colspan="4" scope="col"
                            style="padding: 15px; vertical-align: top;font-size: 10pt">
                            <span>2. Apa saja yang masih perlu dikembangkan pada diri anak saya?</span>
                        </td>
                    </tr>
                    <tr style="border-top: 2px solid #000;">
                        <td height="100" colspan="4" scope="col"
                            style="padding: 15px; vertical-align: top;font-size: 10pt">
                            <span>3. Langkah-langkah apa yang dapat saya lakukan untuk membantu anak saya
                                mengembangkan
                                hal
                                tersebut?</span>
                        </td>
                    </tr>

                </table>
            </div>
            <div class="section">
                <div class="section-title"> Informasi Perkembangan Anak Didik</div>
                <table class="info-table">
                    <tr style="height: 100px;">
                        <td class="value" style="justify-content: space-between;column-span:2">
                            {{ $nilai->refleksi_guru ?? 'Belum Ada Nilai' }}</td>
                    </tr>

                </table>
            </div>
        </div>



        <div class="student-info-header">
            <div class="student-basic-info">
                <div class="section-title">Data Kehadiran Siswa</div>
                <div class="three-column">
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Sakit</td>
                                <td class="value">{{ $absensi?->sakit ?? '-' }} Hari</td>
                            </tr>

                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Izin</td>
                                <td class="value">{{ $absensi?->izin ?? '-' }} Hari</td>
                            </tr>

                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label" widht="50px">Alfa</td>
                                <td class="value">{{ $absensi?->tanpa_keterangan ?? '-' }} Hari</td>
                            </tr>

                        </table>
                    </div>

                </div>
            </div>
        </div>




        <!-- Signature Section -->
        <div class="signature-section" style="margin-bottom:0px; flex-wrap: wrap;">
            <div class="signature-box">
                <div style="margin-bottom:20px "></div>
                <div>Orang Tua/Wali</div>
                <div class="signature-line">
                    <strong>

                        {{ $nilai->siswa->nama_ayah ?? '-' }}

                    </strong>
                </div>
            </div>
            <div class="signature-box">
                <div style="margin-bottom: 0px">{{ ucwords($signature->place) }},
                    {{ isset($signature) ? \Carbon\Carbon::parse($signature->date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                </div>

                <div>Wali Kelas</div>
                <div class="signature-line" style="flex-direction: column; gap: 4px;">
                    <strong style="">

                        {{ $nilai->guru->nama_guru ?? '-' }}

                    </strong>
                    <p style="margin-top: 0; display: block; font-size:7pt">NUPTK: {{ $nilai->guru?->nuptk ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
        <div style="display: flex; justify-content: center; margin-top: 5px;">
            <div class="signature-box">
                <div style="margin-bottom: 0px">Mengetahui
                </div>

                <div>Kepala Sekolah</div>
                <div class="signature-line" style="flex-direction: column; gap: 4px;">
                    <strong>

                        {{ $sekolah->guru?->nama_guru ?? '-' }}

                    </strong>
                    <p style="margin-top: 0; display: block;font-size:7pt">NUPTK: {{ $sekolah->guru?->nuptk ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
        <!-- Print Information -->
        <div class="print-info">
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>


    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }

        // Print function
        function printDocument() {
            window.print();
        }

        // Close window after printing (optional)
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>

</html>
