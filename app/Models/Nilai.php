<?php

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode_id',
        'kelas_id',
        'guru_id',
        'siswa_id',
        'nilai_agama',
        'nilai_jatiDiri',
        'nilai_literasi',
        'nilai_narasi',
        'refleksi_guru',
        'fotoAgama',
        'fotoJatiDiri',
        'fotoLiterasi',
        'fotoNarasi',
    ];

    protected $casts = [
        'fotoAgama' => 'array',
        'fotoJatiDiri' => 'array',
        'fotoLiterasi' => 'array',
        'fotoNarasi' => 'array',
    ];

    // Relationships
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
    // Comment out these relationships until we fix them properly
    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function datamedis() 
    {
        return $this->belongsTo(DataMedisSiswa::class);
    }

    // public function tandatangan()
    // {
    //     return $this->hasOne(TandaTangan::class);
    // }

    public function signature_dates()
    {
        return $this->belongsTo(SignatureDate::class);
    }

   
    /**
     * Get image URLs for a specific field with better debugging
     */
    public function getImageUrls(string $field): array
    {
        try {
            $value = $this->getAttribute($field);

            // Debug logging
            Log::debug("Getting images for field {$field}", [
                'value' => $value,
                'value_type' => gettype($value),
                'record_id' => $this->id
            ]);

            if (empty($value)) {
                return [];
            }

            // If it's already an array (from cast)
            if (is_array($value)) {
                $urls = array_filter(array_map(function ($path) {
                    if (empty($path)) return null;

                    // If it's already a full URL, return as is
                    if (str_starts_with($path, 'http')) {
                        return $path;
                    }

                    // Remove any leading slashes and 'storage/' prefix
                    $cleanPath = ltrim($path, '/');
                    if (str_starts_with($cleanPath, 'storage/')) {
                        $cleanPath = substr($cleanPath, 8); // Remove 'storage/' prefix
                    }

                    // Convert storage path to URL
                    $url = Storage::disk('public')->url($cleanPath);

                    Log::debug("Converted path to URL", [
                        'original_path' => $path,
                        'clean_path' => $cleanPath,
                        'final_url' => $url
                    ]);

                    return $url;
                }, $value));

                return array_values($urls); // Reindex array
            }

            // If it's a JSON string
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $this->processImagePaths($decoded);
                }

                // If it's a single path string
                return $this->processImagePaths([$value]);
            }

            return [];
        } catch (Exception $e) {
            Log::error("Error getting image URLs for field {$field}: " . $e->getMessage(), [
                'record_id' => $this->id,
                'field' => $field,
                'exception' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Process image paths to URLs
     */
    private function processImagePaths(array $paths): array
    {
        return array_filter(array_map(function ($path) {
            if (empty($path)) return null;

            if (str_starts_with($path, 'http')) {
                return $path;
            }

            // Clean the path
            $cleanPath = ltrim($path, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }

            return Storage::disk('public')->url($cleanPath);
        }, $paths));
    }

    /**
     * Debug method to check what's stored in photo fields
     */
    public function debugPhotoFields(): array
    {
        $fields = ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi'];
        $debug = [];

        foreach ($fields as $field) {
            $value = $this->getAttribute($field);
            $debug[$field] = [
                'raw_value' => $value,
                'type' => gettype($value),
                'is_array' => is_array($value),
                'count' => is_array($value) ? count($value) : 0,
                'processed_urls' => $this->getImageUrls($field)
            ];
        }

        return $debug;
    }

    /**
     * Get all photo URLs from all photo fields
     */
    public function getAllPhotoUrls(): array
    {
        $fields = ['fotoAgama', 'fotoJatiDiri', 'fotoLiterasi', 'fotoNarasi'];
        $allPhotos = [];

        foreach ($fields as $field) {
            $photos = $this->getImageUrls($field);
            $allPhotos = array_merge($allPhotos, $photos);
        }

        return $allPhotos;
    }

    /**
     * Get total photo count
     */
    public function getTotalPhotoCount(): int
    {
        return count($this->getAllPhotoUrls());
    }

    /**
     * Check if record has any photos
     */
    public function hasPhotos(): bool
    {
        return $this->getTotalPhotoCount() > 0;
    }
}
