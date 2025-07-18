<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'periode_id',
        'tanggal',
        'sakit',
        'izin',
        'tanpa_keterangan',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sakit' => 'integer',
        'izin' => 'integer',
        'tanpa_keterangan' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    // Accessor for total absence
    public function getTotalAbsenAttribute(): int
    {
        return ($this->sakit ?? 0) + ($this->izin ?? 0) + ($this->tanpa_keterangan ?? 0);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }


}
