<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - {{ $siswa->nama_lengkap }}</title>

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
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if ($sekolah->logo)
                    <img src="{{ Storage::disk('public')->url($sekolah->logo) }}" alt="Logo {{ $sekolah->nama_sekolah }}"
                        class="header-logo">
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
                <h1>Data Siswa</h1>
                <h2>Sistem Informasi Akademik {{ $sekolah->nama_sekolah }} <br>TA: {{ $periode->tahun_ajaran }}</h2>
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
                                <td class="value" style="width: 100px;">
                                    <strong>{{ Str::upper($siswa->nama_lengkap) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">NIS</td>
                                <td class="value">
                                    <strong>
                                        <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;"
                                            width="16" height="16" fill="currentColor" class="bi bi-postcard"
                                            viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm7.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0zM2 5.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1H2.5a.5.5 0 0 1-.5-.5M10.5 5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM13 8h-2V6h2z" />
                                        </svg>
                                        {{ $siswa->nis ?? '-' }}
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">NISN</td>
                                <td class="value">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;"
                                        width="16" height="16" fill="currentColor" class="bi bi-person-vcard"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4m4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5M9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8m1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5" />
                                        <path
                                            d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96q.04-.245.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1 1 0 0 1 1 12z" />
                                    </svg>
                                    {{ $siswa->nisn ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Agama</td>
                                <td class="value">{{ $siswa->agama }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Tempat Lahir</td>
                                <td class="value">{{ $siswa->tempat_lahir }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal Lahir</td>
                                <td class="value">
                                    {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d F Y') : '' }}
                                    <br>
                                    @if ($siswa->tanggal_lahir)
                                        <small>({{ $siswa->tanggal_lahir->age }} tahun)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Jenis Kelamin</td>
                                <td class="value">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <div>
                            <p style="text-align: center">Qr Code</p>

                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(STR::upper($siswa?->nama_lengkap) . '_ NIS: ' . $siswa?->nis . '_ NISN: ' . $siswa?->nisn . '_' . $siswa?->tempat_lahir . ', ' . Carbon\Carbon::parse($siswa?->tanggal_lahir)->translatedFormat('d F Y'), 'QRCODE', 3, 3) }}"
                                alt="barcode" width="65" />

                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Combined Information Sections -->
        <div class="section">
            <div class="section-title">Informasi Tambahan</div>
            <table class="info-table">
                <tr>
                    <td class="label">NIK</td>
                    <td class="value">{{ $siswa->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Kartu Keluarga</td>
                    <td class="value">{{ $siswa->kk ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Berat Badan & Tinggi Badan</td>
                    <td class="value">{{ $datamedis->berat_badan ?? '-' }} KG / {{ $datamedis->tinggi_badan ?? '-' }}
                        CM</td>
                </tr>
                <tr>
                    <td class="label">Golongan Darah</td>
                    <td class="value">{{ $datamedis->golongan_darah ?? 'Tidak Tahu' }}</td>
                </tr>

            </table>
        </div>

        <!-- Parent Information - 3 Column Layout -->
        <div class="section">
            <div class="section-title">Data Orang Tua</div>
            <div class="three-column">
                <div class="column">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Ayah</td>
                            <td class="value">{{ $siswa->nama_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nama Ibu</td>
                            <td class="value">{{ $siswa->nama_ibu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pekerjaan Ayah</td>
                            <td class="value">{{ $siswa->pekerjaan_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pekerjaan Ibu</td>
                            <td class="value">{{ $siswa->pekerjaan_ibu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pendidikan Ayah</td>
                            <td class="value">{{ $siswa->pendidikan_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pendidikan Ibu</td>
                            <td class="value">{{ $siswa->pendidikan_ibu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nomor Telepon</td>
                            <td class="value">{{ $siswa->telepon ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>

        <!-- Address Information - Compact -->
        <div class="section">
            <div class="section-title">Alamat</div>
            <table class="info-table">
                <tr>
                    <td class="label">Alamat Lengkap</td>
                    <td class="value">{{ Str::upper($siswa->alamat ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Kelurahan/Desa</td>
                    <td class="value">{{ $siswa->kelurahan->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kecamatan</td>
                    <td class="value">{{ $siswa->kecamatan->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kota/Kabupaten</td>
                    <td class="value">{{ $siswa->kota->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Provinsi</td>
                    <td class="value">{{ $siswa->provinsi->name ?? '-' }}</td>
                </tr>
            </table>
        </div>



        @php
            use Illuminate\Support\Facades\Auth;
        @endphp

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="student-photo">
                @if ($siswa->foto)
                    <img src="{{ Storage::disk('public')->url($siswa->foto) }}"
                        alt="Foto {{ $siswa->nama_lengkap }}">
                @else
                    <div class="no-photo">
                        <div>FOTO</div>
                        <div>SISWA</div>
                    </div>
                @endif
            </div>
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div>Kepala Sekolah</div>
                <div class="signature-line" style="flex-direction: column; align-items: center;">
                    <strong style="margin-bottom: 5px;">

                        @if (isset($sekolah) && $sekolah)
                            @if ($sekolah->guru)
                                {{ $sekolah->guru->nama_guru }}
                            @elseif($sekolah->kepala_sekolah)
                                {{ $sekolah->kepala_sekolah }}
                            @else
                                Kepala Sekolah
                            @endif
                        @else
                            Kepala Sekolah
                        @endif

                    </strong>
                    <small style="margin-top: 0;">NIP: {{ $sekolah->guru->nip ?? '-' }}</small>
                </div>
            </div>

        </div>
        <!-- Print Information -->
        <div class="print-info">
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>

    <style>
        /* Enhanced signature section styling */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
            page-break-inside: avoid;
            padding: 20px 0;
        }

        /* Adjust photo box size in signature section */
        .signature-section .student-photo {
            width: 3cm;
            height: 4cm;
            border: 1px solid #6b7280;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 4px;
        }

        .signature-section .student-photo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            border-radius: 3px;
        }

        .signature-box {
            text-align: center;
            width: 200px;
            padding: 10px;
        }

        .signature-box div {
            margin-bottom: 8px;
            font-size: 12px;
            line-height: 1.4;
        }

        .signature-line {
            margin-top: 60px;
            padding-top: 8px;
            font-weight: bold;
            font-size: 11px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-line>strong {
            text-decoration: underline;
        }

        .signature-line small {
            font-size: 9px;
            font-weight: normal;
            color: #666;
        }

        /* School footer styling */
        .school-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            page-break-inside: avoid;
        }

        .school-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .school-logo {
            flex-shrink: 0;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
        }

        .school-details {
            flex: 1;
        }

        .school-details h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .school-details p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }

        .print-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
            text-align: center;
            page-break-inside: avoid;
        }

        .print-info p {
            margin-bottom: 3px;
        }

        @media print {
            .signature-section {
                margin-top: 30px;
            }

            .signature-line {
                margin-top: 50px;
            }

            .school-footer {
                margin-top: 25px;
            }

            .logo-img {
                width: 50px;
                height: 50px;
            }
        }
    </style>

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
