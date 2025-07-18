<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Periode;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class GuruPrintController extends Controller
{
    public function printGuru(Guru $guru)
    {
        // Load siswa relationships
        $guru->load(['provinsi', 'kota', 'kecamatan', 'kelurahan']);
        $periode = Periode::where('is_active', true)->first();
        // Get school data with guru relationship
        $sekolah = Sekolah::with('guru')->first();
        return view('guru.print', compact('guru','sekolah','periode'));
    }

    public function printAllGuru()
    {
        $gurus = Guru::with(['provinsi', 'kota', 'kecamatan', 'kelurahan'])->get();
        $periode = Periode::where('is_active', true)->first();
        $sekolah = Sekolah::with('guru')->first();
        return view('guru.report-print', compact('gurus', 'sekolah', 'periode'));
    }
}
