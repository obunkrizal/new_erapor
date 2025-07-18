<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SuratKeputusanGuru extends Model
{
    use HasFactory;

    protected $table = 'surat_keputusan_guru';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'guru_id',
        'jenis_keputusan',
        'status_kepegawaian',
        'jabatan_lama',
        'jabatan_baru',
        'unit_kerja_lama',
        'unit_kerja_baru',
        'tmt_berlaku',
        'tmt_berakhir',
        'dasar_hukum',
        'pertimbangan',
        'isi_keputusan',
        'pejabat_penandatangan',
        'jabatan_penandatangan',
        'nip_penandatangan',
        'file_surat',
        'status',
        'catatan',
        'tanggal_persetujuan',
        'disetujui_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tmt_berlaku' => 'date',
        'tmt_berakhir' => 'date',
        'tanggal_persetujuan' => 'datetime',
       
    ];

    /**
     * Boot method to auto-generate nomor_surat
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_surat)) {
                $model->nomor_surat = $model->generateNomorSurat();
            }
        });
    }

    /**
     * Generate nomor surat automatically with better duplicate handling
     */
    public function generateNomorSurat(): string
    {
        $tanggalSurat = $this->tanggal_surat ? Carbon::parse($this->tanggal_surat) : Carbon::now();
        $year = $tanggalSurat->year;
        $month = $tanggalSurat->format('m');
        
        // Get the highest number for this year and month
        $lastRecord = static::whereYear('tanggal_surat', $year)
            ->whereMonth('tanggal_surat', $tanggalSurat->month)
            ->where('nomor_surat', 'REGEXP', '^[0-9]{3}/SK-ALF/')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_surat, "/", 1) AS UNSIGNED) DESC')
            ->first();

        if ($lastRecord && $lastRecord->nomor_surat) {
            // Extract number from last nomor_surat
            preg_match('/^(\d+)\/SK-ALF\//', $lastRecord->nomor_surat, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Roman months mapping
        $romanMonths = [
            '01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV',
            '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII',
            '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
        ];

        $romanMonth = $romanMonths[$month];
        
        // Keep trying until we get a unique number
        do {
            $nomorSurat = sprintf('%03d/SK-ALF/%s/%d', $newNumber, $romanMonth, $year);
            $exists = static::where('nomor_surat', $nomorSurat)->exists();
            if ($exists) {
                $newNumber++;
            }
        } while ($exists);
        
        return $nomorSurat;
    }

    /**
     * Relationship with User (Guru)
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relationship with User (Penyetuju)
     */
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Scope untuk filter berdasarkan jenis keputusan
     */
    public function scopeJenisKeputusan($query, $jenis)
    {
        return $query->where('jenis_keputusan', $jenis);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk surat yang masih berlaku
     */
    public function scopeBerlaku($query)
    {
        return $query->where('tmt_berlaku', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('tmt_berakhir')
                          ->orWhere('tmt_berakhir', '>=', now());
                    });
    }

    /**
     * Accessor untuk format nomor surat
     */
    public function getFormattedNomorSuratAttribute()
    {
        return $this->nomor_surat;
    }

    /**
     * Accessor untuk status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'dibatalkan' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Accessor untuk jenis keputusan label
     */
    public function getJenisKeputusanLabelAttribute()
    {
        $labels = [
            'pengangkatan' => 'Pengangkatan',
            'promosi' => 'Promosi',
            'mutasi' => 'Mutasi',
            'pemberhentian' => 'Pemberhentian',
            'penugasan_khusus' => 'Penugasan Khusus',
        ];

        return $labels[$this->jenis_keputusan] ?? $this->jenis_keputusan;
    }

    /**
     * Accessor untuk status kepegawaian label
     */
    public function getStatusKepegawaianLabelAttribute()
    {
        $labels = [
            'pns' => 'PNS',
            'pppk' => 'PPPK',
            'gtk_honorer' => 'GTK Honorer',
            'kontrak' => 'Kontrak',
            'gtt' => 'GTT',
            'gty' => 'GTY',
        ];

        return $labels[$this->status_kepegawaian] ?? $this->status_kepegawaian;
    }
}
