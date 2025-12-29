<?php

namespace App\Models;

use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_guru',
        'nip',
        'nuptk',
        'jenis_kelamin',
        'agama',
        'jabatan',
        'pendidikan_terakhir',
        'status_kepegawaian',
        'tempat_lahir',
        'tanggal_lahir',
        'telepon',
        'alamat',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'kelurahan_id',
        'foto',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Get the user that owns the guru profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all classes assigned to this guru
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'guru_id');
    }

    /**
     * Get active classes only
     */
    public function kelasAktif(): HasMany
    {
        return $this->kelas()->where('status', 'aktif');
    }

    /**
     * Get provinsi relationship
     */
    public function provinsi()
    {
        return $this->belongsTo(Province::class, 'provinsi_id');
    }

    /**
     * Get kota relationship
     */
    public function kota()
    {
        return $this->belongsTo(City::class, 'kota_id');
    }

    /**
     * Get kecamatan relationship
     */
    public function kecamatan()
    {
        return $this->belongsTo(District::class, 'kecamatan_id');
    }

    /**
     * Get kelurahan relationship
     */
    public function kelurahan()
    {
        return $this->belongsTo(Village::class, 'kelurahan_id');
    }
}
