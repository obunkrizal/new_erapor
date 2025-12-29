<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DimensiPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'dimensi_pembelajaran';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'deskripsi',
        'urutan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function indikators()
    {
        return $this->hasMany(IndikatorCapaian::class, 'dimensi_id');
    }

    public function templateNarasis()
    {
        return $this->hasMany(TemplateNarasi::class, 'dimensi_id');
    }

    public function penilaianSemesters()
    {
        return $this->hasMany(PenilaianSemester::class, 'dimensi_id');
    }
}