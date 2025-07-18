<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSpp;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class PembayaranSppReportController extends Controller
{
    public function printLaporan(Request $request)
    {
        // Fetch all pembayaran spp records, optionally add filters here
        $pembayaranSpps = PembayaranSpp::with(['siswa', 'periode'])->orderBy('payment_date', 'desc')->get();

        // Fetch sekolah data (assuming single record)
        $sekolah = Sekolah::first();

        // Return a simple print-friendly view with the data
        return view('pembayaran_spp.laporan_print', compact('pembayaranSpps', 'sekolah'));
    }
}
