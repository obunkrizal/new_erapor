<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AutoNarasiGenerator;
use App\Models\{ObservasiHarian, PenilaianSemester, DimensiPembelajaran, Siswa};

class PenilaianPAUDController extends Controller
{
    protected $narasiGenerator;

    public function __construct(AutoNarasiGenerator $narasiGenerator)
    {
        $this->narasiGenerator = $narasiGenerator;
    }

    /**
     * Input observasi harian
     */
    public function storeObservasi(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'indikator_id' => 'required|exists:indikator_capaian,id',
            'tanggal_observasi' => 'required|date',
            'kategori_penilaian' => 'required|in:BB,MB,BSH,BSB',
            'catatan_guru' => 'nullable|string',
            'foto_dokumentasi' => 'nullable|image|max:2048'
        ]);

        $validated['guru_id'] = auth()->id();

        if ($request->hasFile('foto_dokumentasi')) {
            $validated['foto_dokumentasi'] = $request->file('foto_dokumentasi')
                ->store('observasi', 'public');
        }

        $observasi = ObservasiHarian::create($validated);

        return redirect()->back()->with('success', 'Observasi berhasil disimpan');
    }

    /**
     * Generate penilaian semester untuk satu siswa
     */
    public function generatePenilaian(Request $request, $siswa_id)
    {
        $validated = $request->validate([
            'periode_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $penilaian = $this->narasiGenerator->generatePenilaianSemester(
                $siswa_id,
                $validated['periode_id'],
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil di-generate',
                'data' => $penilaian
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate penilaian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate penilaian untuk seluruh kelas
     */
    public function generatePenilaianKelas(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'periode_id' => 'required|integer'
        ]);

        $siswas = Siswa::where('kelas_id', $validated['kelas_id'])->get();

        $hasil = [];

        foreach ($siswas as $siswa) {
            try {
                $penilaian = $this->narasiGenerator->generatePenilaianSemester(
                    $siswa->id,
                    $validated['periode_id']
                );

                $hasil[] = [
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'status' => 'success',
                    'jumlah_dimensi' => count($penilaian)
                ];

            } catch (\Exception $e) {
                $hasil[] = [
                    'siswa_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Generate selesai',
            'data' => $hasil
        ]);
    }

    /**
     * Lihat hasil penilaian semester
     */
    public function showPenilaian($siswa_id, $periode_id)
    {
        $siswa = Siswa::findOrFail($siswa_id);

        $penilaian = PenilaianSemester::where('siswa_id', $siswa_id)
            ->where('periode_id', $periode_id)
            ->with('dimensi')
            ->get();

        $statistik = $this->narasiGenerator->getStatistikPerkembangan(
            $siswa_id,
            $periode_id
        );

        return view('penilaian.show', compact('siswa', 'penilaian', 'statistik', 'periode_id'));
    }

    public function showPenilaianSiswa($siswa_id, $periode_id)
    {
        $siswa = Siswa::findOrFail($siswa_id);

        $penilaian = PenilaianSemester::where('siswa_id', $siswa_id)
            ->where('periode_id', $periode_id)
            ->with('dimensi')
            ->get();

        $statistik = $this->narasiGenerator->getStatistikPerkembangan(
            $siswa_id,
            $periode_id
        );  
        

        $tahun_ajaran = Periode::findOrFail($periode_id)->tahun_ajaran;
        $semester = Periode::findOrFail($periode_id)->semester;
        return view('penilaian.show', compact('siswa', 'penilaian', 'statistik', 'tahun_ajaran', 'semester'));
    }

    /**
     * Edit narasi manual (override auto-generate)
     */
    public function updateNarasi(Request $request, $id)
    {
        $validated = $request->validate([
            'narasi_manual' => 'required|string'
        ]);

        $penilaian = PenilaianSemester::findOrFail($id);
        $penilaian->update($validated);

        return redirect()->back()->with('success', 'Narasi berhasil diperbarui');
    }

    /**
     * Approve penilaian
     */
    public function approvePenilaian(Request $request, $siswa_id)
    {
        $validated = $request->validate([
            'periode_id' => 'required|integer',
        ]);

        PenilaianSemester::where('siswa_id', $siswa_id)
            ->where('periode_id', $validated['periode_id'])
            ->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id()
            ]);

        return redirect()->back()->with('success', 'Penilaian berhasil di-approve');
    }
}