<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'rentang_usia',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_ekstrakurikuler')
            ->withPivot('periode_id', 'capaian')
            ->withTimestamps();
    }
}
