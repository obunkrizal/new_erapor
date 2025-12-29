<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'rentang_usia',
        'kapasitas',
        'guru_id',
        'periode_id',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    // Relationships
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class);
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'kelas_siswa')
                    ->withPivot('status', 'tanggal_masuk', 'tanggal_keluar')
                    ->withTimestamps();
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    // Helper methods for capacity calculations
    public function getJumlahSiswaAktifAttribute(): int
    {
        return $this->kelasSiswa()->where('status', 'aktif')->count();
    }

    public function getSisaKapasitasAttribute(): int
    {
        return ($this->kapasitas ?? 0) - $this->jumlah_siswa_aktif;
    }

    public function getPersentaseKapasitasAttribute(): float
    {
        if (($this->kapasitas ?? 0) == 0) return 0;
        return ($this->jumlah_siswa_aktif / $this->kapasitas) * 100;
    }

    // Add this relationship to your existing Kelas model
    public function hargaSpp(): HasMany
    {
        return $this->hasMany(HargaSpp::class);
    }

    public function getHargaSpp()
    {
        return $this->hargaSpp()->where('is_active', true)->first()?->harga ?? 0;
    }
}
