<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PindahKelas extends Model
{
    use HasFactory;

    protected $table = 'pindah_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_asal_id',
        'kelas_tujuan_id',
        'periode_id',
        'user_id',
        'tanggal_pindah',
        'status',
        'alasan_pindah',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pindah' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasAsal(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
