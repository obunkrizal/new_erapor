<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Sekolah;
use App\Models\DataMedisSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index()
    {
        // Your index logic
    }

    public function show(Nilai $nilai)
    {
        // Your show logic
    }

    public function print(Nilai $nilai)
    {
        // Check authorization
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        // Additional authorization checks
        $user = Auth::user();
        if ($user->isGuru()) {
            // Guru can only print their own assessments
            if ($nilai->guru_id !== $user->guru->id) {
                abort(403, 'You can only print your own assessments');
            }
        }
        // Admin can print all assessments

        // Load necessary relationships
        $nilai->load([
            'siswa',
            'kelas',
            'guru',
            'periode'
        ]);

        // Get school information
        $sekolah = Sekolah::first();

        // Get medical data for the student
        $datamedis = DataMedisSiswa::where('siswa_id', $nilai->siswa_id)->first();

        // Get attendance data
        $absensi = $this->getAttendanceData($nilai);

        return view('nilai.print', compact(
            'nilai',
            'sekolah',
            'datamedis',
            'absensi'
        ));
    }

    private function getAttendanceData(Nilai $nilai)
    {
        try {
            // Adjust this based on your attendance model structure
            // Example if you have an Absensi model:
            /*

            $absensi = \App\Models\Absensi::where('siswa_id', $nilai->siswa_id)
                ->where('periode_id', $nilai->periode_id)
                ->selectRaw('
                    SUM(CASE WHEN status = "sakit" THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = "izin" THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as tanpa_keterangan
                ')
                ->first();
            */

            // For now, return default values
            return (object) [
                'sakit' => 0,
                'izin' => 0,
                'tanpa_keterangan' => 0
            ];
        } catch (\Exception $e) {
            return (object) [
                'sakit' => 0,
                'izin' => 0,
                'tanpa_keterangan' => 0
            ];
        }
    }
}
