<?php

use Illuminate\Support\Facades\Route;
use App\Exports\SiswaImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Resources\Siswas\Pages\PrintReport;
use App\Http\Controllers\PembayaranSppReportController;
use App\Filament\Resources\Nilais\Pages\NilaiStats;
use App\Http\Controllers\PrintSuratKeputusanController;
use App\Http\Controllers\PembayaranSppPrintController;
use App\Http\Controllers\GuruPrintController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NilaiPrintController;
use App\Http\Controllers\SiswaPrintController;
use App\Http\Controllers\NilaiController;
use App\Models\Sekolah;

// Route::get('/', function () {
//     return view('home');
// });

Route::get('/generate-siswa-import-template', function () {
    $filePath = public_path('templates/siswa_import_template.xlsx');
    Excel::store(new SiswaImportTemplateExport, 'siswa_import_template.xlsx', 'public');
    return "Siswa import template generated at: " . $filePath;
});

Route::get('/', function () {
    // Assuming you have a way to get school data
    $sekolah = Sekolah::first(); // Replace with actual school model
    return view('home', compact('sekolah'));
})->name('home');


Route::get('/siswa/{siswa}/print', [SiswaPrintController::class, 'print'])
    ->name('siswa.print')
    ->middleware('auth');
Route::get('/siswa/{siswa}/print-cover', [SiswaPrintController::class, 'printCover'])
    ->name('siswa.print-cover')
    ->middleware('auth');

Route::get('/nilai/{nilai}/print', [NilaiPrintController::class, 'print'])
    ->name('nilai.print')
    ->middleware('auth');

Route::get('/gurusiswakelas/{kelasSiswa}/print', [\App\Http\Controllers\GuruSiswaKelasPrintController::class, 'print'])
    ->name('gurusiswakelas.print')
    ->middleware('auth');

Route::get('/gurusiswakelas/{kelasSiswa}/print-cover', [\App\Http\Controllers\GuruSiswaKelasPrintController::class, 'printCover'])
    ->name('gurusiswakelas.print-cover')
    ->middleware('auth');

Route::get('/nilai/print-bulk', [NilaiPrintController::class, 'printBulk'])
    ->name('nilai.print-bulk')
    ->middleware('auth');

Route::get('/guru/{guru}/print', [GuruPrintController::class, 'printGuru'])
    ->name('guru.print')
    ->middleware('auth');

Route::get('/guru/print-report', [GuruPrintController::class, 'printAllGuru'])
    ->name('guru.print-report')
    ->middleware('auth');

Route::post('/siswa/print-multiple', [SiswaPrintController::class, 'printMultiple'])
    ->name('siswa.print-multiple')
    ->middleware('auth');
Route::middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/nilai/create', [NilaiController::class, 'create'])->name('nilai.create');
    Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
    Route::get('/nilai/{nilai}/edit', [NilaiController::class, 'edit'])->name('nilai.edit');
    Route::put('/nilai/{nilai}', [NilaiController::class, 'update'])->name('nilai.update');
    Route::delete('/nilai/{nilai}', [NilaiController::class, 'destroy'])->name('nilai.destroy');
});

// View and print accessible by both admin and guru
Route::middleware(['auth'])->group(function () {
    Route::get('/nilai/{nilai}', [NilaiPrintController::class, 'show'])->name('nilai.show');
    Route::get('/nilai/{nilai}/print', [NilaiPrintController::class, 'print'])->name('nilai.print');
});
Route::get('/pembayaran-spp/{id}/print-invoice', [PembayaranSppPrintController::class, 'printInvoice'])->name('pembayaran-spp.print-invoice');

Route::get('pembayaran-spp/print-laporan', [PembayaranSppReportController::class, 'printLaporan'])->name('pembayaran-spp.print-laporan');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/siswas/print-report', [PrintReport::class, 'render'])
        ->name('filament.resources.siswas.print-report');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/nilai/stats', NilaiStats::class)->name('nilai.stats');
});
// Print routes for Surat Keputusan
Route::get('/surat-keputusan/{suratKeputusan}/print', [PrintSuratKeputusanController::class, 'print'])
    ->name('surat-keputusan.print');

Route::get('/surat-keputusan/{suratKeputusan}/download', [PrintSuratKeputusanController::class, 'download'])
    ->name('surat-keputusan.download');
