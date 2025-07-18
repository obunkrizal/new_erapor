<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataMedisSiswa extends Model
{
    protected $fillable = [
        'periode_id',
        'kelas_id',
        'siswa_id',
        'tanggal_pemeriksaan',
        'tinggi_badan',
        'berat_badan',
        'golongan_darah',
        'riwayat_penyakit',
        'catatan',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
