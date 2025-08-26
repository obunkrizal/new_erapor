<?php

namespace Tests\Feature;

use App\Models\PembayaranSpp;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\Kelas;
use App\Models\HargaSpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranSppNonTagihanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_remaining_payment_with_pending_status_if_invoice_does_not_start_with_tagihan()
    {
        // Create necessary records with all required fields
        $siswa = Siswa::create([
            'nama_lengkap' => 'Jane Doe',
            'nis' => '2526.0002',
            'nisn' => '1234567891',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'agama' => 'Islam',
            'alamat' => 'Jl. Test No. 456',
            'nama_ayah' => 'Ayah Doe',
            'nama_ibu' => 'Ibu Doe',
            'pekerjaan_ayah' => 'PNS',
            'pekerjaan_ibu' => 'Ibu Rumah Tangga',
            'telepon' => '081234567891',
            'status' => 'Aktif'
        ]);

        $periode = Periode::create([
            'nama_periode' => 'Semester 1 2023/2024',
            'tahun_ajaran' => '2023/2024',
            'semester' => 'ganjil',
            'is_active' => true
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => '10A',
            'status' => 'aktif'
        ]);

        // Create HargaSpp record to set the nominal price
        $hargaSpp = HargaSpp::create([
            'periode_id' => $periode->id,
            'kelas_id' => $kelas->id,
            'harga' => 200000, // Set nominal price higher than payment amount
            'is_active' => true
        ]);

        // Create a payment record with amount less than nominal
        $payment = PembayaranSpp::create([
            'siswa_id' => $siswa->id,
            'periode_id' => $periode->id,
            'kelas_id' => $kelas->id,
            'month' => 'February',
            'no_inv' => 'REGULAR-20230201-00001', // Does NOT start with TAGIHAN
            'amount' => 100000, // Less than nominal (200000)
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'pending',
            'catatan' => 'Initial payment',
        ]);

        // Create a remaining payment
        $remainingPayment = $payment->createTagihanSisa();

        // Assert that the remaining payment status is 'pending' (not paid)
        $this->assertEquals('pending', $remainingPayment->status);
    }
}
