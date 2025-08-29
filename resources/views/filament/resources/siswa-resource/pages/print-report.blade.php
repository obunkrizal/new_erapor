<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Siswa</title>
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
                <h1>Laporan Data Siswa</h1>
                <h2>Sistem Informasi Akademik {{ $sekolah->nama_sekolah }}</h2>
            </div>
        </div>
        <div class="section">
            <table class="info-table">
                <thead>
                    <tr class="section-title" style="text-align: center; font-size:7pt">
                        <th>No.</th>
                        <th>NIS / NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Jenis Kelamin</th>
                        <th>Agama</th>
                        <th>Nama Ayah</th>
                        <th>Nama Ibu</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($siswas) && $siswas->count())
                        @foreach ($siswas as $siswa)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->nis }} / {{ $siswa->nisn }} </td>
                                <td>
                                    <div>
                                        {{ $siswa->nama_lengkap }}
                                        <br>
                                        <small class="text-bold">{{ $siswa->kk }}</small>
                                    </div>
                                </td>
                                <td>{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir?->format('d M Y ') }}</td>
                                <td>{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $siswa->agama }}</td>
                                <td>{{ $siswa->nama_ayah }}</td>
                                <td>{{ $siswa->nama_ibu }}</td>
                                <td>{{ $siswa->telepon }}</td>
                                <td>{{ $siswa->alamat }}, {{ $siswa->kelurahan->name }},
                                    {{ $siswa->kecamatan->name }}, {{ $siswa->kota->name }},
                                    {{ $siswa->provinsi->name }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="11" style="text-align:center;">No siswa data available.</td>
                        </tr>
                    @endif
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

                                    <br><small>NUPTK: {{ $sekolah->guru->nuptk ?? '-' }}</small>
                               
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
