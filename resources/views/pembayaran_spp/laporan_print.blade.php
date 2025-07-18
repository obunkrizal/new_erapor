<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran SPP</title>
     <link href="{{ asset('css/filament/print/printpage.css') }}" rel="stylesheet">
     <link href="{{ asset('css/filament/print/printpage.css') }}" rel="stylesheet">
     <style>
        .container {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 70px 1cm 1cm 1cm;
            background: white;
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
                <h1>Laporan Pembayaran SPP</h1>
                <h2>Sistem Informasi Akademik {{$sekolah->nama_sekolah}}</h2>
            </div>
        </div>

        <!-- Student Info Header with Photo -->
        <div class="section">
            <table class="info-table">
                <thead>
                    <tr class="section-title" style="text-align: center; font-size:7pt">
                        <th>No. Invoice</th>
                        <th>Nama Siswa</th>
                        <th>Periode</th>
                        <th>Kelas/Rombel</th>
                        <th>Jumlah Bayar</th>
                        <th>Bulan</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Metode Pembayaran</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody style="vertical-align:middle;text-align:center">
                    @foreach ($pembayaranSpps as $pembayaran)
                    <tr>
                        <td>{{ $pembayaran->no_inv }}</td>
                        <td>{{ $pembayaran->siswa->nama_lengkap ?? 'N/A' }}</td>
                        <td>{{ $pembayaran->periode->tahun_ajaran ?? 'N/A' }}</td>
                        <td>{{ $pembayaran->kelas->nama_kelas ?? 'N/A' }}</td>
                        <td>{{ number_format($pembayaran->amount, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($pembayaran->month) }}</td>
                        <td>{{ \Illuminate\Support\Str::of($pembayaran->payment_date)->isEmpty() ? 'N/A' : \Carbon\Carbon::parse($pembayaran->payment_date)->format('d F Y') }}</td>
                        <td>{{ ucfirst($pembayaran->payment_method) }}</td>
                        <td>{{ ucfirst($pembayaran->status) }}</td>
                        <td>{{ ucfirst($pembayaran->catatan) }}</td>
                    </tr>
                    @endforeach
                </tbody>
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
                                    <br><small>NIP: {{ $sekolah->guru->nip ?? '-' }}</small>
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
