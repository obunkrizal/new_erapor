<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Data Semua Guru</title>
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
                <h1>Laporan Data Guru</h1>
                <h2>Sistem Informasi Akademik {{ $sekolah->nama_sekolah }}</h2>
            </div>
        </div>

          <div class="section">
            <table class="info-table">
                <thead>
                    <tr class="section-title" style="text-align: center; font-size:7pt">
                    <th>No</th>
                    <th style="width: 150px">Nama Lengkap</th>
                    <th>NUPTK</th>
                    <th>Jenis Kelamin</th>
                    <th>Jabatan</th>
                    <th>Agama</th>
                    <th>Tempat, Tanggal Lahir</th>
                    <th>Telepon</th>
                    <th>Alamat</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($gurus as $index => $guru)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $guru->nama_guru ?? '-' }}</td>
                        <td>{{ $guru->nuptk ?? '-' }}</td>
                        <td>{{ $guru->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>{{ $guru->jabatan??'-' }}</td>
                        <td>{{ $guru->agama ??'-' }}</td>
                        <td>{{ ucwords($guru->tempat_lahir ?? '-') }}, {{ $guru->tanggal_lahir ? $guru->tanggal_lahir->format('d F Y') : '-' }}</td>
                        <td>{{ $guru->telepon ?? '-' }}</td>
                        <td>{{ $guru->alamat ?? '-' }}, {{ $guru->kelurahan->name ?? '-' }}, {{ $guru->kecamatan->name ?? '-' }}, {{ $guru->kota->name ?? '-' }}, {{ $guru->provinsi->name ?? '-' }} </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div>Kepala Sekolah</div>
                <div class="signature-line" >
                    <strong>
                        @if (isset($sekolah) && $sekolah)
                            @if ($sekolah->guru)
                                {{ $sekolah->guru->nama_guru }}
                                @if ($sekolah->guru->nip)
                                    <br /><small>NUPTK: {{ $sekolah->guru->nuptk }}</small>
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

        <div class="print-info">
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>

    <script>
        function printDocument() {
            window.print();
        }
    </script>
</body>

</html>
