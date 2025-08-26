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

    @if($pembayaran->is_lunas || str_starts_with($pembayaran->no_inv, 'TAGIHAN'))
        <div class="watermark">[LUNAS]</div>
        <div class="watermark2">Terima Kasih</div>
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
                                <p style="text-align: center">ScanMe!</p>
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
                <tr>
                    <td class="label">Status</td>
                    @if ($pembayaran->is_lunas)
                        <td class="value" style="color: green; font-weight: bold;">LUNAS
                            <br><span style="font-weight: normal; font-size: 0.9em;">(Lunas pada
                                @if ($pembayaran->tanggal_pelunasan)
                                    {{ Carbon\Carbon::parse($pembayaran->tanggal_pelunasan)->translatedFormat('d F Y') }}
                                @else
                                    {{ Carbon\Carbon::parse($pembayaran->updated_at)->translatedFormat('d F Y') }}
                                @endif
                            )</span>
                        </td>
                    @elseif (str_starts_with($pembayaran->no_inv, 'TAGIHAN') && $pembayaran->status == 'paid')
                        <td class="value" style="color: green; font-weight: bold;">LUNAS
                            <br><span style="font-weight: normal; font-size: 0.9em;">(Tagihan sisa pembayaran - Status otomatis lunas)</span>
                        </td>
                    @else
                        <td class="value" style="color: red; font-weight: bold;">BELUM LUNAS
                            <br><span style="font-weight: normal; font-size: 0.9em;">Sisa Pembayaran {{ formatRupiah($pembayaran->nominal - $pembayaran->amount) }} untuk bulan {{ $pembayaran->month }} - Silahkan segera membayar kekurangannya</span>
                        </td>
                    @endif
                </tr>
            </table>
        </div>

        <!-- Billing Information for Remaining Payments - Only show if payment is not complete and not a remaining payment for an already paid original -->
        @php
            $showBillingInfo = !$pembayaran->is_lunas && !$pembayaran->original_payment_id;

            // If this is a remaining payment (tagihan sisa), check if the original payment is already lunas
            if ($pembayaran->original_payment_id) {
                $originalPayment = \App\Models\PembayaranSpp::find($pembayaran->original_payment_id);
                if ($originalPayment && $originalPayment->is_lunas) {
                    $showBillingInfo = false;
                }
            }
        @endphp

        @if ($showBillingInfo)
        <div class="section" style="border: 2px solid #dc3545; background-color: #fff5f5;margin-top: 20px; padding: 10px;">
            <div class="section-title" style="color: #dc3545; background-color: white;">⚠️ INFORMASI TAGIHAN SISA PEMBAYARAN</div>
            <table class="info-table">
                <tr>
                    <td class="label" style="width: 40%;">Status Pembayaran</td>
                    <td class="value" style="color: #dc3545; font-weight: bold;">
                        BELUM LUNAS - PERLU DITAGIHKAN
                    </td>
                </tr>
                <tr>
                    <td class="label">Nominal SPP Bulan {{ ucwords($pembayaran->month) }}</td>
                    <td class="value">{{ formatRupiah($pembayaran->nominal) }}</td>
                </tr>
                <tr>
                    <td class="label">Jumlah yang Sudah Dibayar</td>
                    <td class="value">{{ formatRupiah($pembayaran->amount) }}</td>
                </tr>
                <tr>
                    <td class="label">Sisa Pembayaran yang Perlu Ditagih</td>
                    <td class="value" style="color: #dc3545; font-weight: bold;">
                        {{ formatRupiah($pembayaran->sisa_pembayaran) }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Keterangan Tagihan</td>
                    <td class="value">
                        Sisa pembayaran untuk bulan {{ $pembayaran->month }} akan ditagihkan secara otomatis.
                        Silakan hubungi administrasi sekolah untuk informasi lebih lanjut.
                    </td>
                </tr>
            </table>
        </div>
        @endif

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
                                @if ($sekolah->guru->nuptk)
                                    <br><small>NUPTK: {{ $sekolah->guru->nuptk }}</small>
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
