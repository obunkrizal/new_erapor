<?php

namespace Tests\Feature;

use App\Models\PembayaranSpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranSppInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_lunas_status_for_tagihan_invoices_in_invoice_view()
    {
        // Create a payment record with TAGIHAN invoice number
        $payment = PembayaranSpp::create([
            'no_inv' => 'TAGIHAN-20230201-00001', // Starts with TAGIHAN
            'amount' => 50000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'pending',
            'catatan' => 'Test payment',
        ]);

        // Test the invoice view
        $response = $this->get("/pembayaran-spp/{$payment->id}/print-invoice");

        $response->assertStatus(200);
        $response->assertSee('LUNAS', false); // Should show LUNAS status
        $response->assertDontSee('BELUM LUNAS', false); // Should not show BELUM LUNAS
        $response->assertSee('Tagihan sisa pembayaran - Status otomatis lunas', false);
    }

    /** @test */
    public function it_displays_belum_lunas_status_for_non_tagihan_invoices_in_invoice_view()
    {
        // Create a payment record with regular invoice number
        $payment = PembayaranSpp::create([
            'no_inv' => 'REGULAR-20230101-00001', // Does NOT start with TAGIHAN
            'amount' => 50000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'pending',
            'catatan' => 'Test payment',
        ]);

        // Test the invoice view
        $response = $this->get("/pembayaran-spp/{$payment->id}/print-invoice");

        $response->assertStatus(200);
        $response->assertSee('BELUM LUNAS', false); // Should show BELUM LUNAS status
        $response->assertDontSee('Tagihan sisa pembayaran - Status otomatis lunas', false);
    }
}
