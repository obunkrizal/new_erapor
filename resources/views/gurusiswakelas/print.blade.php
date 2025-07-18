<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - {{ $kelasSiswa->siswa->nama_lengkap }}</title>
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
               <h2>Sistem Informasi Akademik {{$sekolah->nama_sekolah}} <br>TA: {{$periode->tahun_ajaran}}</h2>
            </div>
        </div>

        <!-- Student Info Header with Photo -->
        <div class="student-info-header">
            <div class="student-photo">
                @if ($kelasSiswa->siswa->foto)
                    <img src="{{ Storage::disk('public')->url($kelasSiswa->siswa->foto) }}" alt="Foto {{ $kelasSiswa->siswa->nama_lengkap }}">
                @else
                    <div class="no-photo">
                        <div>FOTO</div>
                        <div>SISWA</div>
                    </div>
                @endif
            </div>

            <div class="student-basic-info">
                <div class="section-title">Data Pribadi</div>
                <div class="three-column">
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama Lengkap</td>
                                <td class="value"><strong>{{ Str::upper($kelasSiswa->siswa->nama_lengkap) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">NIS</td>
                                <td class="value"><strong>{{ $kelasSiswa->siswa->nis ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">NISN</td>
                                <td class="value">{{ $kelasSiswa->siswa->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Agama</td>
                                <td class="value">{{ $kelasSiswa->siswa->agama }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Tempat Lahir</td>
                                <td class="value">{{ $kelasSiswa->siswa->tempat_lahir }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal Lahir</td>
                                <td class="value">
                                    {{ $kelasSiswa->siswa->tanggal_lahir ? $kelasSiswa->siswa->tanggal_lahir->format('d F Y') : '' }}
                                    <br>
                                    @if ($kelasSiswa->siswa->tanggal_lahir)
                                        <small>({{ $kelasSiswa->siswa->tanggal_lahir->age }} tahun)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Jenis Kelamin</td>
                                <td class="value">{{ $kelasSiswa->siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr style="text-align: center">
                                <p style="text-align: center">Qr Code</p>
                                <td style="color: #666; vertical-align: bottom; width: 100px; align:right;">
                                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(STR::upper($kelasSiswa->siswa?->nama_lengkap) . '_ NIS: ' . $kelasSiswa->siswa?->nis . '_ NISN: ' . $kelasSiswa->siswa?->nisn . '_' . $kelasSiswa->siswa?->tempat_lahir . ', ' . Carbon\Carbon::parse($kelasSiswa->siswa?->tanggal_lahir)->translatedFormat('d F Y'), 'QRCODE', 3, 3) }}"
                                        alt="barcode" width="65" />
                                </td>
                            </tr>


                        </table>
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
                    <td class="value">{{ $kelasSiswa->siswa->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Kartu Keluarga</td>
                    <td class="value">{{ $kelasSiswa->siswa->kk ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Berat Badan & Tinggi Badan</td>
                    <td class="value">{{ $datamedis->berat_badan ?? '-' }} KG / {{$datamedis->tinggi_badan ?? '-'}} CM</td>
                </tr>
                <tr>
                    <td class="label">Golongan Darah</td>
                    <td class="value">{{ $datamedis->golongan_darah ?? '-' }}</td>
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
                            <td class="value">{{ $kelasSiswa->siswa->nama_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Nama Ibu</td>
                            <td class="value">{{ $kelasSiswa->siswa->nama_ibu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pekerjaan Ayah</td>
                            <td class="value">{{ $kelasSiswa->siswa->pekerjaan_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pekerjaan Ibu</td>
                            <td class="value">{{ $kelasSiswa->siswa->pekerjaan_ibu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pendidikan Ayah</td>
                            <td class="value">{{ $kelasSiswa->siswa->pendidikan_ayah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pendidikan Ibu</td>
                            <td class="value">{{ $kelasSiswa->siswa->pendidikan_ibu ?? '-' }}</td>
                        </tr>
                         <tr>
                    <td class="label">Nomor Telepon</td>
                    <td class="value">{{ $kelasSiswa->siswa->telepon ?? '-' }}</td>
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
                    <td class="value">{{ Str::upper($kelasSiswa->siswa->alamat ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Kelurahan/Desa</td>
                    <td class="value">{{ $kelasSiswa->siswa->kelurahan->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kecamatan</td>
                    <td class="value">{{ $kelasSiswa->siswa->kecamatan->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Kota/Kabupaten</td>
                    <td class="value">{{ $kelasSiswa->siswa->kota->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Provinsi</td>
                    <td class="value">{{ $kelasSiswa->siswa->provinsi->name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Registration Information -->
        <div class="section">
            <div class="section-title">Informasi Pendaftaran</div>
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal Pendaftaran</td>
                    <td class="value">{{ $kelasSiswa->siswa->created_at->format('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Terakhir Diperbarui</td>
                    <td class="value">{{ $kelasSiswa->siswa->updated_at->format('d F Y H:i') }}</td>
                </tr>
            </table>
        </div>

        @php
            use Illuminate\Support\Facades\Auth;
        @endphp

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div>Kepala Sekolah</div>
                <div class="signature-line">
                    <strong>

                        @if (isset($sekolah) && $sekolah)
                            @if ($sekolah->guru)
                                {{ $sekolah->guru->nama_guru }}
                                @if ($sekolah->guru->nip)
                                    <br><small>NIP: {{ $sekolah->guru->nip }}</small>
                                @endif
                            @elseif($sekolah->kepala_sekolah)
                                {{ $sekolah->kepala_sekolah }}
                            @else
                                Kepala Sekolah
                            @endif
                        @else
                            Kepala Sekolah
                        @endif

                    </strong>
                </div>
            </div>
            <div class="signature-box">
                <div>{{ now()->format('d F Y') }}</div>
                <div>Wali Kelas</div>
                <div class="signature-line">
                    <strong>

                        @if (isset($sekolah) && $sekolah)
                            @if ($sekolah->admin_name)
                                {{ $sekolah->admin_name }}
                            @elseif($sekolah->petugas_admin)
                                {{ $sekolah->petugas_admin }}
                            @elseif(Auth::check())
                                {{ Auth::user()->name }}
                            @else
                                Wali Kelas
                            @endif
                        @elseif(Auth::check())
                            {{ Auth::user()->name }}
                        @else
                            Wali Kelas
                        @endif

                    </strong>
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
