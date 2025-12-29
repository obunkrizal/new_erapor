<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_periode',
        'tahun_ajaran',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active periods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive periods
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to filter by semester
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope to filter by academic year
     */
    public function scopeByTahunAjaran($query, $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Get the active period
     */
    public static function getActivePeriode()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Set this period as active and deactivate others
     */
    public function setAsActive()
    {
        // Deactivate all other periods
        self::where('id', '!=', $this->id)->update(['is_active' => false]);

        // Activate this period
        $this->update(['is_active' => true]);
    }

    /**
     * Check if this period is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get formatted semester name
     */
    public function getFormattedSemesterAttribute()
    {
        return ucfirst($this->semester);
    }

    /**
     * Get full period name
     */
    public function getFullNameAttribute()
    {
        return $this->nama_periode . ' (' . $this->tahun_ajaran . ')';
    }

     public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function hargaSpp(): HasMany
    {
        return $this->hasMany(HargaSpp::class);
    }

    public function getActiveHargaSpp()
    {
        return $this->hargaSpp()->where('is_active', true)->get();
    }

    public function penilaianSemester(): BelongsTo
    {
        return $this->belongsTo(PenilaianSemester::class);
    }
}
