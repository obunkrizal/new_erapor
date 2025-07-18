<?php

namespace App\Http\Controllers;

use App\Models\DataMedisSiswa;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiswaPrintController extends Controller
{
    public function print(Siswa $siswa)
    {
        // Load siswa relationships
        $siswa->load(['provinsi', 'kota', 'kecamatan', 'kelurahan']);
        $periode = Periode::where('is_active', true)->first();
        $datamedis=DataMedisSiswa::where('siswa_id',$siswa->id)->first();
        // Get school data with guru relationship
        $sekolah = Sekolah::with('guru')->first();

        return view('siswa.print', compact('siswa', 'sekolah','periode','datamedis'));
    }
    public function printCover(Siswa $siswa)
    {
        // Load siswa relationships
        $siswa->load(['provinsi', 'kota', 'kecamatan', 'kelurahan']);
        $guru = Guru::all();
        $periode = Periode::where('is_active', true)->first();
        $datamedis = DataMedisSiswa::where('siswa_id', $siswa->id)->first();
        // Get school data with guru relationship
        $sekolah = Sekolah::with('guru')->first();

        return view('siswa.print-cover', compact('siswa', 'sekolah','periode','datamedis'));
    }


    public function printMultiple(Request $request)
    {
        $siswaIds = $request->input('siswa_ids', []);

        if (empty($siswaIds)) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih untuk dicetak.');
        }

        $siswas = Siswa::with(['provinsi', 'kota', 'kecamatan', 'kelurahan'])
            ->whereIn('id', $siswaIds)
            ->get();

        // Get school data with guru relationship
        $sekolah = Sekolah::with('guru')->first();

        return view('siswa.print-multiple', compact('siswas', 'sekolah'));
    }
}
