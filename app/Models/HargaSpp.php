<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaSpp extends Model
{
    use HasFactory;

    protected $table = 'harga_spp';

    protected $fillable = [
        'periode_id',
        'kelas_id',
        'tingkat_kelas',
        'harga',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the periode that owns the HargaSpp
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    /**
     * Get the kelas that owns the HargaSpp
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope for active prices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific periode
     */
    public function scopeForPeriode($query, $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    /**
     * Get formatted price
     */
    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    /**
     * Get price for specific kelas or tingkat
     */
    public static function getHargaForKelas($periodeId, $kelasId = null, $tingkatKelas = null)
    {
        $query = static::where('periode_id', $periodeId)->where('is_active', true);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        } elseif ($tingkatKelas) {
            $query->where('tingkat_kelas', $tingkatKelas);
        }

        return $query->first()?->harga ?? 0;
    }
}
