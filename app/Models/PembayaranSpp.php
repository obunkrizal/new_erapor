<?php

namespace App\Models;

use App\Filament\Resources\PembayaranSpps\PembayaranSppResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_spps';

    protected $fillable = [
        'siswa_id',
        'periode_id',
        'kelas_id',
        'month',
        'no_inv',
        'amount',
        'payment_date',
        'tanggal_pelunasan',
        'payment_method',
        'status',
        'catatan',
        'original_payment_id'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Get the harga spp associated with this payment
     */
    public function spp()
    {
        return $this->hasOne(HargaSpp::class, 'periode_id', 'periode_id')
            ->where(function ($query) {
                $query->where('kelas_id', $this->kelas_id)
                      ->from('kelas')
                      ->where('id', $this->kelas_id);
            })
            ->where('is_active', true);
    }

    /**
     * Get the SPP nominal (price) for this payment
     */
    public function getNominalAttribute()
    {
        if ($this->spp) {
            return $this->spp->harga;
        }

        // Fallback: try to get price using the static method from resource
        if ($this->periode_id && $this->kelas_id) {
            return PembayaranSppResource::getHargaSppForKelas(
                $this->periode_id,
                $this->kelas_id
            );
        }

        return 0;
    }

    /**
     * Check if payment is fully paid (lunas)
     */
    public function getIsLunasAttribute()
    {
        $nominal = $this->nominal;
        return $nominal > 0 && abs($nominal - $this->amount) < 0.01;
    }

    /**
     * Get payment status in Indonesian (lunas/belum lunas)
     */
    public function getStatusPembayaranAttribute()
    {
        return $this->is_lunas ? 'LUNAS' : 'BELUM LUNAS';
    }

    /**
     * Update payment for a specific month if it is less than the nominal amount.
     */
    public function updatePaymentForMonth($month)
    {
        if ($this->month === $month) {
            $nominal = $this->nominal;
            if ($this->amount < $nominal) {
                $this->amount = $nominal;
                $this->save();
            }
        }
    }

    /**
     * Get the remaining balance that needs to be billed
     */
    public function getSisaPembayaranAttribute()
    {
        $nominal = $this->nominal;
        $remaining = $nominal - $this->amount;
        return max(0, $remaining); // Return 0 if amount exceeds nominal
    }

    /**
     * Check if payment has remaining balance that needs to be billed
     */
    public function getPerluDitagihAttribute()
    {
        return $this->sisa_pembayaran > 0;
    }

    /**
     * Get formatted remaining balance in Rupiah
     */
    public function getSisaPembayaranFormattedAttribute()
    {
        return 'Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.');
    }

    /**
     * Create a new billing record for the remaining amount
     */
    public function createTagihanSisa()
    {
        if (!$this->perlu_ditagih) {
            return null;
        }

        // Create a new billing record for the remaining amount
        $tagihanSisa = new PembayaranSpp();
        $tagihanSisa->siswa_id = $this->siswa_id;
        $tagihanSisa->periode_id = $this->periode_id;
        $tagihanSisa->kelas_id = $this->kelas_id;
        $tagihanSisa->month = $this->month;
        $tagihanSisa->no_inv = 'TAGIHAN-' . now()->format('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $tagihanSisa->amount = $this->sisa_pembayaran;
        $tagihanSisa->payment_date = now(); // Set to current date for billing record
        $tagihanSisa->payment_method = 'cash'; // Set to a default value for billing record

        // If original invoice starts with TAGIHAN, set status to paid since this is a remaining payment
        if (str_starts_with($this->no_inv, 'TAGIHAN')) {
            $tagihanSisa->status = 'paid';
        } else {
            $tagihanSisa->status = 'pending';
        }

        $tagihanSisa->catatan = 'Tagihan sisa pembayaran bulan ' . $this->month . ' dari invoice ' . $this->no_inv;
        $tagihanSisa->original_payment_id = $this->id; // Track which payment this billing is for

        $tagihanSisa->save();
        return $tagihanSisa;
    }

    /**
     * Check if this payment is for a remaining balance and update original payment status if paid
     */
    public function updateOriginalPaymentStatusIfPaid()
    {
        // If this is a billing record for a remaining payment and it's being paid
        if ($this->original_payment_id && $this->status === 'paid') {
            $originalPayment = PembayaranSpp::find($this->original_payment_id);

            if ($originalPayment && !$originalPayment->is_lunas) {
                // Check if the combined payments now cover the full amount
                $totalPaid = $originalPayment->amount + $this->amount;

                if (abs($totalPaid - $originalPayment->nominal) < 0.01) {
                    // Update the original payment to reflect full payment
                    $originalPayment->amount = $originalPayment->nominal;
                    $originalPayment->status = 'paid';
                    $originalPayment->tanggal_pelunasan = now(); // Set the date of payment completion

                    // Add note about completion through remaining payment
                    $existingNote = $originalPayment->catatan ?? '';
                    $completionNote = "\nPelunasan dilakukan melalui tagihan: " . $this->no_inv .
                        " pada " . now()->format('d F Y');

                    // Only add the note if it's not already there to avoid duplication
                    if (strpos($existingNote, $completionNote) === false) {
                        $originalPayment->catatan = $existingNote . $completionNote;
                    }

                    $originalPayment->save();

                    // Log the status update for debugging
                    Log::info('Original payment status updated to LUNAS', [
                        'original_payment_id' => $originalPayment->id,
                        'original_invoice' => $originalPayment->no_inv,
                        'remaining_payment_id' => $this->id,
                        'remaining_invoice' => $this->no_inv,
                        'total_paid' => $totalPaid,
                        'nominal' => $originalPayment->nominal
                    ]);
                }
            }
        }
    }

    /**
     * Check if this payment has any remaining payments that are paid
     * This ensures the status is updated even if the original payment is modified
     */
    public function checkRemainingPaymentsStatus()
    {
        // If this is an original payment, check if all remaining payments are paid
        if (!$this->original_payment_id) {
            $remainingPayments = PembayaranSpp::where('original_payment_id', $this->id)
                ->where('status', 'paid')
                ->get();

            $totalRemainingPaid = $remainingPayments->sum('amount');
            $totalPaid = $this->amount + $totalRemainingPaid;

            if (abs($totalPaid - $this->nominal) < 0.01 && !$this->is_lunas) {
                // Update to fully paid status
                $this->status = 'paid';
                $this->tanggal_pelunasan = now();

                // Add note about completion through remaining payments
                $existingNote = $this->catatan ?? '';
                $completionNote = "\nPelunasan dilakukan melalui " . $remainingPayments->count() .
                    " tagihan sisa pada " . now()->format('d F Y');

                if (strpos($existingNote, $completionNote) === false) {
                    $this->catatan = $existingNote . $completionNote;
                }

                $this->save();

                Log::info('Payment status updated to LUNAS through remaining payments check', [
                    'payment_id' => $this->id,
                    'invoice' => $this->no_inv,
                    'total_paid' => $totalPaid,
                    'nominal' => $this->nominal,
                    'remaining_payments_count' => $remainingPayments->count()
                ]);
            }
        }
    }

    /**
     * Boot method to handle payment status updates
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($pembayaran) {
            $pembayaran->updateOriginalPaymentStatusIfPaid();
            $pembayaran->checkRemainingPaymentsStatus();
        });
    }
}
