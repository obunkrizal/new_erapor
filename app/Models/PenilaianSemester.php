<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenilaianSemester extends Model
{
    use HasFactory;

    protected $table = 'penilaian_semester';

    protected $fillable = [
        'siswa_id',
        'periode_id',
        'dimensi_id',
        'kategori_akhir',
        'narasi_auto',
        'narasi_manual',
        'is_approved',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Get the user that owns the PenilaianSemester
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function dimensi()
    {
        return $this->belongsTo(DimensiPembelajaran::class, 'dimensi_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getNarasiFinal()
    {
        return $this->narasi_manual ?? $this->narasi_auto;
    }
}
