<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sekolah extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'alamat',
        'no_telp',
        'email',
        'akreditasi',
        'guru_id',
        'logo',
        'logo_yayasan',
        'website',
        'status',
        'visi',
        'misi',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Define the relationship to Guru
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    // Alternative method names for flexibility
    public function kepalaSekolah(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
    // Alternative method names for flexibility
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}
