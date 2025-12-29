<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateNarasi extends Model
{
    use HasFactory;

    protected $table = 'template_narasi';

    protected $fillable = [
        'dimensi_id',
        'kategori_penilaian',
        'template_kalimat',
        'placeholder_options'
    ];

    protected $casts = [
        'placeholder_options' => 'array',
    ];

    public function dimensi()
    {
        return $this->belongsTo(DimensiPembelajaran::class, 'dimensi_id');
    }
}