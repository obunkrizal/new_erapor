<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'nama_lengkap', // Make sure this is the correct column name
        'nis',
        'nisn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'telepon_ayah',
        'telepon_ibu',
        'foto',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'kelurahan_id',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Generate NIS with format: YYYY.SEQUENCE
     * Example: 2526.0001, 2526.0002, etc.
     * Where:
     * - 2526 represents academic year 2025/2026
     * - 0001 is the student sequence number
     */
    public static function generateNIS(): string
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        // Get last 2 digits of current year and next year
        $yearPrefix = substr($currentYear, -2) . substr($nextYear, -2);

        // Create the base pattern for this academic year
        $basePattern = $yearPrefix . '.';

        // Get the last NIS for current academic year
        $lastNIS = self::where('nis', 'like', $basePattern . '%')
            ->orderBy('nis', 'desc')
            ->first();

        if ($lastNIS) {
            // Extract the sequence number from the last NIS
            $nisParts = explode('.', $lastNIS->nis);
            $lastSequence = isset($nisParts[1]) ? (int) $nisParts[1] : 0;
            $newSequence = $lastSequence + 1;
        } else {
            // First student for this academic year
            $newSequence = 1;
        }

        // Format sequence number with leading zeros (4 digits)
        $sequenceFormatted = str_pad($newSequence, 4, '0', STR_PAD_LEFT);

        return $basePattern . $sequenceFormatted;
    }

    /**
     * Generate NIS based on active periode
     */
    public static function generateNISByPeriode(): string
    {
        // Get active periode
        $activePeriode = \App\Models\Periode::where('is_active', true)->first();

        if ($activePeriode && $activePeriode->tahun_ajaran) {
            // Extract years from tahun_ajaran (e.g., "2025/2026")
            $years = explode('/', $activePeriode->tahun_ajaran);
            if (count($years) == 2) {
                $yearPrefix = substr($years[0], -2) . substr($years[1], -2);
            } else {
                // Fallback to current year
                $currentYear = now()->year;
                $nextYear = $currentYear + 1;
                $yearPrefix = substr($currentYear, -2) . substr($nextYear, -2);
            }
        } else {
            // Fallback to current year
            $currentYear = now()->year;
            $nextYear = $currentYear + 1;
            $yearPrefix = substr($currentYear, -2) . substr($nextYear, -2);
        }

        // Create the base pattern for this academic year
        $basePattern = $yearPrefix . '.';

        // Get the last NIS for current academic year
        $lastNIS = self::where('nis', 'like', $basePattern . '%')
            ->orderBy('nis', 'desc')
            ->first();

        if ($lastNIS) {
            // Extract the sequence number from the last NIS
            $nisParts = explode('.', $lastNIS->nis);
            $lastSequence = isset($nisParts[1]) ? (int) $nisParts[1] : 0;
            $newSequence = $lastSequence + 1;
        } else {
            // First student for this academic year
            $newSequence = 1;
        }

        // Format sequence number with leading zeros (4 digits)
        $sequenceFormatted = str_pad($newSequence, 4, '0', STR_PAD_LEFT);

        return $basePattern . $sequenceFormatted;
    }

    /**
     * Get next available NIS for preview
     */
    public static function getNextNIS(): string
    {
        return self::generateNISByPeriode();
    }

    /**
     * Parse NIS to get components
     */
    public static function parseNIS(string $nis): array
    {
        $parts = explode('.', $nis);

        if (count($parts) !== 2) {
            return [
                'valid' => false,
                'year_code' => null,
                'sequence' => null,
                'academic_year' => null,
            ];
        }

        $yearCode = $parts[0];
        $sequence = $parts[1];

        // Convert year code to academic year
        if (strlen($yearCode) === 4) {
            $year1 = '20' . substr($yearCode, 0, 2);
            $year2 = '20' . substr($yearCode, 2, 2);
            $academicYear = $year1 . '/' . $year2;
        } else {
            $academicYear = 'Invalid';
        }

        return [
            'valid' => true,
            'year_code' => $yearCode,
            'sequence' => $sequence,
            'academic_year' => $academicYear,
        ];
    }

    /**
     * Get academic year from NIS
     */
    public function getAcademicYearFromNIS(): string
    {
        $parsed = self::parseNIS($this->nis);
        return $parsed['academic_year'] ?? 'Unknown';
    }

    /**
     * Get sequence number from NIS
     */
    public function getSequenceFromNIS(): string
    {
        $parsed = self::parseNIS($this->nis);
        return $parsed['sequence'] ?? 'Unknown';
    }

    /**
     * Boot method to auto-generate NIS
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($siswa) {
            if (empty($siswa->nis)) {
                $siswa->nis = self::generateNISByPeriode();
            }
        });
    }

    /**
     * Define searchable attributes for global search
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_lengkap', 'nis', 'nisn'];
    }

    /**
     * Get all kelas siswa for the siswa
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class);
    }

    /**
     * Get current active kelas siswa
     */
    public function kelasAktif()
    {
        return $this->kelasSiswa()->where('status', 'aktif')->with('kelas');
    }

    // Relationships for address
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'provinsi_id');
    }

    public function kota(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'kota_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'kecamatan_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'kelurahan_id');
    }
}
