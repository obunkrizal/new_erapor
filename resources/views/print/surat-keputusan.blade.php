<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Surat Keputusan - {{ $surat->nomor_surat }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
            .print-button {
                display: none;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: none;
                margin: 0;
                padding: 2cm 1cm 1cm 1cm;
                width: 100%;
            }
        }
    </style>
</head>
<body class="font-sans text-xs leading-tight text-black bg-white">
    <button class="print-button fixed top-[70px] right-5 bg-blue-900 text-white py-2 px-5 rounded cursor-pointer z-[1100] hover:bg-blue-400" onclick="window.print()">🖨️ Print</button>
    <div class="container max-w-[210mm] mx-auto pt-[60px] px-4 pb-4 relative bg-white">

        <!-- Header -->
        <div class="header fixed top-0 left-0 w-full flex items-center justify-between py-0.5 border-b border-gray-400 bg-white z-50 mb-px">
            <div class="flex items-center gap-4 header-left flex-2">
                <div class="flex-shrink-0 w-12 h-12 overflow-hidden rounded header-logo">
                    @if ($sekolah->logo)
                        <img src="{{ Storage::disk('public')->url($sekolah->logo) }}" alt="Logo {{ $sekolah->nama_sekolah }}" class="object-contain w-full h-full rounded" />
                    @else
                        <span class="text-lg font-bold text-primary">Logo</span>
                    @endif
                </div>
                <div class="header-info">
                    <h4 class="text-sm font-bold mb-0.5 text-gray-700">Pemerintah Provinsi DKI Jakarta</h4>
                    <p class="text-xs text-gray-500 mb-0.5">Dinas Pendidikan</p>
                    <p class="text-xs text-gray-500 mb-0.5">Jl. Gatot Subroto Kav. 40-41, Jakarta Selatan 12190</p>
                    <p class="text-xs text-gray-500 mb-0.5">Telp: (021) 525-9142, Fax: (021) 525-4371</p>
                    <p class="text-xs text-gray-500 mb-0.5">Website: www.jakarta.go.id, Email: disdik@jakarta.go.id</p>
                </div>
            </div>
            <div class="flex-1 text-center header-right">
                <h1 class="text-sm font-bold mb-0.5 text-gray-700">Surat Keputusan</h1>
                <h2 class="text-xs font-normal text-gray-500">Nomor: {{ $surat->nomor_surat }}</h2>
            </div>
        </div>

        <!-- Title -->
        <div class="mt-20 mb-2 text-center title">
            <h3 class="text-lg font-semibold uppercase">Surat Keputusan</h3>
        </div>

        <!-- Nomor dan Tanggal -->
        <div class="mb-2 text-center nomor-tanggal">
            <p><strong>Nomor: {{ $surat->nomor_surat }}</strong></p>
        </div>

        <!-- Perihal -->
        <div class="mb-4 text-center content-tentang">
            <p class="font-semibold uppercase">Tentang</p>
            <p>{{ $surat->perihal }}</p>
        </div>

        <!-- Informasi Guru -->
        <div class="mb-4 section">
            <table class="w-full text-xs bg-white border-collapse table-info">
                <tbody>
                    <tr>
                        <td class="w-2/5 px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Nama</td>
                        <td class="px-1 colon">:</td>
                        <td class="w-full px-2 py-1 border border-gray-300 value">{{ $surat->guru->name }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Jenis Keputusan</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ ucfirst($surat->jenis_keputusan) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Status Kepegawaian</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ strtoupper($surat->status_kepegawaian) }}</td>
                    </tr>
                    @if($surat->jabatan_lama)
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Jabatan Lama</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->jabatan_lama }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Jabatan Baru</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->jabatan_baru }}</td>
                    </tr>
                    @if($surat->unit_kerja_lama)
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Unit Kerja Lama</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->unit_kerja_lama }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">Unit Kerja Baru</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->unit_kerja_baru }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">TMT Berlaku</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->tmt_berlaku->locale('id')->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    @if($surat->tmt_berakhir)
                    <tr>
                        <td class="px-2 py-1 font-semibold text-gray-700 bg-gray-100 border border-gray-300 label">TMT Berakhir</td>
                        <td class="px-1 colon">:</td>
                        <td class="px-2 py-1 border border-gray-300 value">{{ $surat->tmt_berakhir->locale('id')->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Dasar Hukum -->
        <div class="mb-4 section">
            <div class="px-2 py-1 mb-1 text-xs font-bold text-white uppercase bg-gray-500 rounded section-title">Menimbang:</div>
            <div class="text-sm decision-content">{{ $surat->dasar_hukum }}</div>
        </div>

        <!-- Pertimbangan -->
        <div class="mb-4 section">
            <div class="px-2 py-1 mb-1 text-xs font-bold text-white uppercase bg-gray-500 rounded section-title">Mengingat:</div>
            <div class="text-sm decision-content">{{ $surat->pertimbangan }}</div>
        </div>

        <!-- Isi Keputusan -->
        <div class="mb-4 section">
            <div class="text-sm decision-content">{{ $surat->isi_keputusan }}</div>
        </div>

        <!-- Penutup -->
        <div class="mb-4 content-closing">
            <p>Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.</p>
        </div>

        <!-- Signature -->
        <div class="flex justify-between mt-5 signature page-break-inside-avoid" role="contentinfo" aria-label="Signature section">
            <div>
                <p>Ditetapkan di Jakarta</p>
                <p>pada tanggal {{ $surat->tanggal_surat->locale('id')->isoFormat('D MMMM Y') }}</p>
                <br />
                <p class="font-bold">{{ $surat->jabatan_penandatangan }}</p>
                <div class="signature-line border-t border-gray-400 mt-7 pt-1 font-bold flex items-center justify-center text-gray-700 min-h-[20px]"></div>
                <p class="font-bold">{{ $surat->pejabat_penandatangan }}</p>
                @if($surat->nip_penandatangan)
                <p class="signature-nip">NIP. {{ $surat->nip_penandatangan }}</p>
                @endif
            </div>
        </div>

        <div class="clear"></div>

        <!-- Print Information -->
        <div class="print-info fixed bottom-0 left-0 w-full bg-white border-t border-gray-300 text-[9px] text-gray-500 text-center py-2 page-break-inside-avoid" role="contentinfo" aria-label="Print information">
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB</p>
            <p>Dokumen ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</body>
</html>
