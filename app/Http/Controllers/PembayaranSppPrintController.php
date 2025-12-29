<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Siswa;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Models\PembayaranSpp;

class PembayaranSppPrintController extends Controller
{
    /**
     * Display the invoice for the given PembayaranSpp record.
     *
     * @param int $id
     * @return View
     */
    public function printInvoice($id)
    {
        $sekolah = Sekolah::first();
        $pembayaran = PembayaranSpp::with(['siswa', 'periode','guru'])->findOrFail($id);

        // Use stored invoice number if exists, otherwise generate
        $invoiceNumber = $pembayaran->no_inv ?? ('INV-' . now()->format('Ymd') . '-' . str_pad($pembayaran->id, 5, '0', STR_PAD_LEFT));

        return view('pembayaran_spp.invoice', compact('pembayaran','sekolah', 'invoiceNumber'));
    }
}
