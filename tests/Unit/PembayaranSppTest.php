<?php

namespace Tests\Unit;

use App\Models\PembayaranSpp;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\Kelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranSppTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_payment_for_month_if_less_than_nominal()
    {
        // Create related records first
        $periode = Periode::create([
            'nama_periode' => 'Semester 1 2024/2025',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'ganjil'
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'X IPA 1', 'periode_id' => $periode->id]);
        $siswa = Siswa::create(['nama' => 'Test Siswa', 'nis' => '12345', 'kelas_id' => $kelas->id]);

        $pembayaran = PembayaranSpp::create([
            'siswa_id' => $siswa->id,
            'periode_id' => $periode->id,
            'kelas_id' => $kelas->id,
            'month' => 'july',
            'no_inv' => 'INV-001',
            'amount' => 5000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'pending',
            'catatan' => 'Test payment',
        ]);

        // Assume nominal amount is 10000
        $pembayaran->updatePaymentForMonth('july');

        $this->assertEquals(10000, $pembayaran->amount);
    }

    /** @test */
    public function it_does_not_update_payment_if_amount_is_equal_or_greater_than_nominal()
    {
        // Create related records first
        $periode = Periode::create([
            'nama_periode' => 'Semester 1 2024/2025',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'ganjil'
        ]);
        $kelas = Kelas::create(['nama_kelas' => 'X IPA 1', 'periode_id' => $periode->id]);
        $siswa = Siswa::create(['nama' => 'Test Siswa', 'nis' => '12345', 'kelas_id' => $kelas->id]);

        $pembayaran = PembayaranSpp::create([
            'siswa_id' => $siswa->id,
            'periode_id' => $periode->id,
            'kelas_id' => $kelas->id,
            'month' => 'august',
            'no_inv' => 'INV-002',
            'amount' => 15000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'pending',
            'catatan' => 'Test payment',
        ]);

        // Assume nominal amount is 10000
        $pembayaran->updatePaymentForMonth('august');

        $this->assertEquals(15000, $pembayaran->amount);
    }
}
