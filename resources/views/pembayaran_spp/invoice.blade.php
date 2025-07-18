<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembayaran SPP - {{ $pembayaran->siswa->nama_lengkap }}</title>
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
@php
    include_once app_path('Helpers/FormatHelper.php');
@endphp
    <button class="print-button no-print" onclick="window.print()">🖨️ Print</button>

    @if(isset($pembayaran->amount) && $pembayaran->amount > 0)
    <div class="watermark">LUNAS</div>
    @endif

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
                <h1>Invoice Pembayaran SPP</h1>
                <h2>Sistem Informasi Akademik {{$sekolah->nama_sekolah}} {{$pembayaran->periode->tahun_ajaran}}</h2>
            </div>
        </div>

        <!-- Student Info Header with Photo -->
        <div class="student-info-header">
            <div class="student-basic-info">
                <div class="section-title">Data Pembayaran</div>
                <div class="three-column">
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label" style="width: 100%">No</td>
                                <td class="value"><strong>{{ Str::upper($pembayaran->no_inv) }}</strong></td>
                            </tr>


                            <tr>
                                <td class="label">Periode Ajaran</td>
                                <td class="value"><strong>{{ $pembayaran->periode->tahun_ajaran ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Kelas/Rombel</td>
                                <td class="value"><strong>{{ $pembayaran->kelas->nama_kelas ?? '-' }}</strong></td>
                            </tr>

                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama Siswa</td>
                                <td class="value">{{ Str::upper($pembayaran->siswa->nama_lengkap ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td class="label">NIS/NISN</td>
                                <td class="value">
                                    {{ $pembayaran->siswa->nis ?? '-' }} / {{ $pembayaran->siswa->nisn ?? '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr style="text-align: center">
                                <p style="text-align: center">Qr Code</p>
                                <td style="color: #666; vertical-align: bottom; width: 100px; align:right;">
                                    @if ($pembayaran->siswa)
                                        <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG(
                                            STR::upper($pembayaran->siswa->nama_lengkap) .
                                            '_ NIS: ' . $pembayaran->siswa->nis .
                                            '_ NISN: ' . $pembayaran->siswa->nisn .
                                            '_' . ' Bayar: ' . formatRupiah($pembayaran->amount) .
                                            '_ ' . Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->translatedFormat('d F Y'),
                                            'QRCODE', 3, 3) }}"
                                            alt="barcode" width="65" />
                                    @endif
                                </td>
                            </tr>


                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combined Information Sections -->
        <div class="section">
            <div class="section-title">Detail Pembayaran</div>
            <table class="info-table">
                <tr>
                <tr>
                    <td class="label">Tanggal Pembayaran</td>
                    <td class="value">
                        <strong>{{ Carbon\Carbon::parse($pembayaran->payment_date)->translatedFormat('d F Y') }}</strong>
                    </td>
                </tr>
                </tr>
                <tr>
                    <td class="label">Untuk Pembayaran Bulan</td>
                    <td class="value"><strong>{{ ucwords($pembayaran->month) }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Jumlah Bayar</td>
                    <td class="value">{{ formatRupiah($pembayaran->amount) ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Terbilang</td>
                    <td class="value">{{ ucwords(terbilang($pembayaran->amount) ? terbilang($pembayaran->amount) . ' rupiah' : '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td class="value">{{ Ucwords($pembayaran->payment_method  ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Catatan/ Keterangan</td>
                    <td class="value">{{ Ucwords($pembayaran->catatan  ?? '-') }}</td>
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
                <div>Petugas Administrasi</div>
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
                                Petugas Administrasi
                            @endif
                        @elseif(Auth::check())
                            {{ Auth::user()->name }}
                        @else
                            Petugas Administrasi
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
