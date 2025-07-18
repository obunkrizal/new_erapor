<?php

namespace App\Http\Controllers;

use App\Models\KelasSiswa;
use App\Models\Guru;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\DataMedisSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruSiswaKelasPrintController extends Controller
{
    public function print(KelasSiswa $kelasSiswa)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        $user = Auth::user();
        if ($user && (method_exists($user, 'isGuru') ? $user->isGuru() : ($user->role ?? '') === 'guru')) {
            if ($kelasSiswa->kelas->guru_id !== $user->guru->id) {
                abort(403, 'You can only print your own class students');
            }
        }

        $kelasSiswa->load(['siswa', 'kelas', 'kelas.guru']);
        $periode = Periode::where('is_active', true)->first();
        $datamedis = DataMedisSiswa::where('siswa_id', $kelasSiswa->siswa_id)->first();
        $sekolah = Sekolah::with('guru')->first();

        return view('gurusiswakelas.print', compact('kelasSiswa', 'sekolah', 'periode', 'datamedis'));
    }

    public function printCover(KelasSiswa $kelasSiswa)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        $user = Auth::user();
        if ($user && (method_exists($user, 'isGuru') ? $user->isGuru() : ($user->role ?? '') === 'guru')) {
            if ($kelasSiswa->kelas->guru_id !== $user->guru->id) {
                abort(403, 'You can only print your own class students');
            }
        }

        $kelasSiswa->load(['siswa', 'kelas', 'kelas.guru']);
        $guru = Guru::all();
        $periode = Periode::where('is_active', true)->first();
        $datamedis = DataMedisSiswa::where('siswa_id', $kelasSiswa->siswa_id)->first();
        $sekolah = Sekolah::with('guru')->first();

        return view('gurusiswakelas.print-cover', compact('kelasSiswa', 'sekolah', 'periode', 'datamedis', 'guru'));
    }
}
