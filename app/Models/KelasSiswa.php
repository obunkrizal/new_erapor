<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelasSiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'periode_id',
        'status',
        'tanggal_masuk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'status' => 'string',
    ];

    /**
     * Get the siswa that owns the kelas siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Get the kelas that owns the kelas siswa
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Get the periode that owns the kelas siswa
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    /**
     * Scope to get active students
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope to get by periode
     */
    public function scopeByPeriode($query, $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    /**
     * Scope to get by kelas
     */
    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }
}
