<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\SuratKeputusanGuru;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PrintSuratKeputusanController extends Controller
{
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid filename characters
        $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
        return str_replace($invalidChars, '_', $filename);
    }

    public function print(SuratKeputusanGuru $suratKeputusan)
    {
        $sekolah=Sekolah::first();
        $surat=SuratKeputusanGuru::first();
        $data = [
            'surat' => $suratKeputusan,
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
        ];
        return view('print.surat-keputusan', compact('data','surat','sekolah'));
    }

    public function download(SuratKeputusanGuru $suratKeputusan)
    {
        $sekolah = Sekolah::first();
        $data = [
            'surat' => $suratKeputusan,
            'tanggal_cetak' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
        ];

        $pdf = PDF::loadView('print.surat-keputusan', $data, compact('suratKeputusan','sekolah'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = "Surat_Keputusan_" . $this->sanitizeFilename($suratKeputusan->nomor_surat) . ".pdf";

        return $pdf->download($filename);
    }
}
