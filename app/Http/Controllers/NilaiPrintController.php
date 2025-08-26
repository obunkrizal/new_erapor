<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Models\SignatureDate;
use App\Models\DataMedisSiswa;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class NilaiPrintController extends Controller
{
    public function print(Nilai $nilai, Request $request)
    {
        // Temporarily allow access for testing
        // Check authorization
        // if (!Auth::check()) {
        //     abort(403, 'Unauthorized access');
        // }

        // Additional authorization checks
        $user = Auth::user();
        if ($user && property_exists($user, 'role') && $user->role === 'guru') {
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
            'periode',
            'absensi',
        ]);

        // Get school information
        $sekolah = Sekolah::first();

        // Get medical data for the student
        $datamedis = DataMedisSiswa::where('siswa_id', $nilai->siswa_id)->first();
        $signature = SignatureDate::first();

        // Get attendance data
        $absensi = $this->getAttendanceData($nilai);

        // Validation: check if signature date, attendance, medical data, and input scores exist
        $missingScores = false;
        $requiredScores = [
            'nilai_agama',
            'nilai_jatiDiri',
            'nilai_literasi',
            'nilai_narasi',
            'refleksi_guru',
        ];

        foreach ($requiredScores as $score) {
            if (empty($nilai->$score)) {
                $missingScores = true;
                break;
            }
        }

        if (!$signature || !$datamedis || !$absensi ||
            ($absensi->sakit == 0 && $absensi->izin == 0 && $absensi->tanpa_keterangan == 0) || $missingScores) {
            $errorMessage = 'Printing tidak diizinkan jika tanggal tanda tangan, absensi, data medis, atau input nilai belum terisi. Harap di isi terlebih dahulu';

            return view('nilai.print', compact(
                'nilai',
                'sekolah',
                'datamedis',
                'absensi',
                'signature'
            ))->with('errorMessage', $errorMessage);
        }

        return view('nilai.print', compact(
            'nilai',
            'sekolah',
            'datamedis',
            'absensi',
            'signature'
        ));
    }

    public function printBulk(Request $request)
    {
        // Check authorization
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        // Check if signature date exists
        $signatureDateExists = \App\Models\SignatureDate::exists();
        if (!$signatureDateExists) {
            Notification::make()
                ->title('Printing Tidak di Izinkan')
                ->danger()
                ->body('Printing Tidak di Izinkan jika tanggal tandatangan belum di input.')
                ->send();

            return redirect()->back();
        }

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            Notification::make()
                ->title('No Nilai Selected')
                ->danger()
                ->body('Please select at least one nilai to print.')
                ->send();

            return redirect()->back();
        }

        // Load Nilai records with relationships
        $nilais = Nilai::with(['siswa', 'kelas', 'guru', 'periode', 'absensi'])
            ->whereIn('id', $ids)
            ->get();

        // Load school information and signature
        $sekolah = Sekolah::first();
        $signature = SignatureDate::first();

        // Load medical data and attendance for each Nilai
        foreach ($nilais as $nilai) {
            $nilai->datamedis = DataMedisSiswa::where('siswa_id', $nilai->siswa_id)->first();
            $nilai->absensi_data = $this->getAttendanceData($nilai);
        }

        $date = $request->query('date', null);

        return view('nilai.print-bulk', compact(
            'nilais',
            'sekolah',
            'date',
            'signature'
        ));
    }

    public function printWithDate(Nilai $nilai, $date)
    {
        // Check authorization
        if (!Auth::check()) {
            abort(403, 'Unauthorized access');
        }

        // Additional authorization checks
        $user = Auth::user();
        if ($user && property_exists($user, 'role') && $user->role === 'guru') {
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
            'periode',
            'absensi'
        ]);

        // Get school information
        $sekolah = Sekolah::first();

        // Get medical data for the student
        $datamedis = DataMedisSiswa::where('siswa_id', $nilai->siswa_id)->first();

        // Get attendance data (you might need to adjust this based on your attendance model)
        $absensi = $this->getAttendanceData($nilai);

        return view('nilai.print', compact(
            'nilai',
            'sekolah',
            'datamedis',
            'absensi',
            'date'
        ));
    }

    private function getAttendanceData(Nilai $nilai)
    {
        try {
            $absensiData = \App\Models\Absensi::where('siswa_id', $nilai->siswa_id)
                ->where('periode_id', $nilai->periode_id)
                ->selectRaw('SUM(sakit) as sakit, SUM(izin) as izin, SUM(tanpa_keterangan) as tanpa_keterangan')
                ->first();

            return (object) [
                'sakit' => $absensiData->sakit ?? 0,
                'izin' => $absensiData->izin ?? 0,
                'tanpa_keterangan' => $absensiData->tanpa_keterangan ?? 0,
            ];
        } catch (\Exception $e) {
            return (object) [
                'sakit' => 0,
                'izin' => 0,
                'tanpa_keterangan' => 0,
            ];
        }
    }
}
