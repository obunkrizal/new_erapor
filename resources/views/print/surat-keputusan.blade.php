<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Surat Keputusan - {{ $surat->nomor_surat }}</title>
    <link href="{{ asset('css/filament/print/printpage.css') }}" rel="stylesheet">
    <style>
        .header {
            position: static;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #6b7280;
            background: white;
            z-index: 1000;
            margin-bottom: 1cm;
        }

        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
        }
    </style>
</head>

<body class="font-sans text-xs leading-tight text-black bg-white">
    <button
        class="print-button fixed top-[70px] right-5 bg-blue-900 text-white py-2 px-5 rounded cursor-pointer z-[1100] hover:bg-blue-400"
        onclick="window.print()">🖨️ Print</button>
    <div class="container">

        <!-- Header -->
        <div >
            <div>
                <div style="text-align: center">
                    @if ($sekolah->logo_yayasan)
                        <img style="width: 80px;" src="{{ Storage::disk('public')->url($sekolah->logo_yayasan) }}"
                            alt="Logo {{ $sekolah->nama_sekolah }}" />
                    @else
                        <span class="text-lg font-bold text-primary">Logo</span>
                    @endif
                </div>
            </div>
            <div style="text-align: center">
                <h4 class="mb-1 text-gray-700">YAYASAN PENDIDIKAN </h4>
                <H4 class="mb-1 text-gray-500">AL-FURQON CENDEKIA</p>
                    <p class="label">Jl. Gatot Subroto Kav. 40-41, Jakarta Selatan 12190</p>
                    <p class="mb-1 text-gray-500">Telp: (021) 525-9142, Fax: (021) 525-4371</p>
                    <p class="mb-1 text-gray-500">Website: www.jakarta.go.id, Email: disdik@jakarta.go.id</p>
            </div>

        </div>

        <!-- Title -->
        <div style="text-align: center; margin-top: 0.5cm">
            <h3>Surat Keputusan</h3>
        </div>

        <!-- Nomor dan Tanggal -->
        <div style="text-align: center">
            <p><strong>Nomor: {{ $surat->nomor_surat }}</strong></p>
        </div>

        <!-- Perihal -->
        <div style="text-align: center; margin-top: 0.5cm">
            <p class="font-semibold uppercase">Tentang</p>
            <p>{{ $surat->perihal }}</p>
        </div>

        <!-- Informasi Guru -->
        <div class="mb-6 section">
            <table>
                <tbody>
                    <tr>
                        <td class="label">Nama</td>
                        <td>:</td>
                        <td>{{ $surat->guru->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Keputusan</td>
                        <td>:</td>
                        <td>{{ ucfirst($surat->jenis_keputusan) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status Kepegawaian</td>
                        <td>:</td>
                        <td>{{ strtoupper($surat->status_kepegawaian) }}</td>
                    </tr>
                    @if ($surat->jabatan_lama)
                        <tr>
                            <td class="label">Jabatan Lama</td>
                            <td>:</td>
                            <td>{{ $surat->jabatan_lama }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Jabatan Baru</td>
                        <td>:</td>
                        <td>{{ $surat->jabatan_baru }}</td>
                    </tr>
                    @if ($surat->unit_kerja_lama)
                        <tr>
                            <td class="label">Unit Kerja Lama</td>
                            <td>:</td>
                            <td>{{ $surat->unit_kerja_lama }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Unit Kerja Baru</td>
                        <td>:</td>
                        <td>{{ $surat->unit_kerja_baru }}</td>
                    </tr>
                    <tr>
                        <td class="label">TMT Berlaku</td>
                        <td>:</td>
                        <td>{{ $surat->tmt_berlaku->locale('id')->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    @if ($surat->tmt_berakhir)
                        <tr>
                            <td class="label">TMT Berakhir</td>
                            <td>:</td>
                            <td>{{ $surat->tmt_berakhir->locale('id')->isoFormat('D MMMM Y') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Dasar Hukum -->
        <div class="mb-6 section">
            <div class="section-title"
                style="background-color:#6b7280; color:#fff; padding:0.25rem 0.5rem; font-weight:bold; text-transform:uppercase; border-radius:0.25rem; font-size:0.75rem; margin-bottom:0.5rem;">
                Menimbang:</div>
            <div class="decision-content">{{ $surat->dasar_hukum }}</div>
        </div>

        <!-- Pertimbangan -->
        <div class="mb-6 section">
            <div class="section-title"
                style="background-color:#6b7280; color:#fff; padding:0.25rem 0.5rem; font-weight:bold; text-transform:uppercase; border-radius:0.25rem; font-size:0.75rem; margin-bottom:0.5rem;">
                Mengingat:</div>
            <div class="decision-content">{{ $surat->pertimbangan }}</div>
        </div>

        <!-- Isi Keputusan -->
        <div class="mb-6 section">
            <div class="decision-content">{{ $surat->isi_keputusan }}</div>
        </div>

        <!-- Penutup -->
        <div class="mb-6 content-closing">
            <p>Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat
                kekeliruan akan diperbaiki sebagaimana mestinya.</p>
        </div>

        <!-- Signature -->
        <div class="signature" role="contentinfo" aria-label="Signature section">
            <div>
                <p>Ditetapkan di Jakarta</p>
                <p>pada tanggal {{ $surat->tanggal_surat->locale('id')->isoFormat('D MMMM Y') }}</p>
                <br />
                <p>{{ $surat->jabatan_penandatangan }}</p>
                <div class="signature-line"></div>
                <p>{{ $surat->pejabat_penandatangan }}</p>
                @if ($surat->nip_penandatangan)
                    <p class="signature-nip">NIP. {{ $surat->nip_penandatangan }}</p>
                @endif
            </div>
        </div>

        <!-- Print Information -->
        <div class="print-info" role="contentinfo" aria-label="Print information">
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</body>

</html>
