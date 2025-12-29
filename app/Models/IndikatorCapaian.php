<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorCapaian extends Model
{
    use HasFactory;

    protected $table = 'indikator_capaian';

    protected $fillable = [
        'dimensi_id',
        'kode_indikator',
        'deskripsi',
        'rentang_usia',
        'urutan'
    ];

    public function dimensi()
    {
        return $this->belongsTo(DimensiPembelajaran::class, 'dimensi_id');
    }

    public function observasis()
    {
        return $this->hasMany(ObservasiHarian::class, 'indikator_id');
    }
}