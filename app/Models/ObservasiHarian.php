<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservasiHarian extends Model
{
    use HasFactory;

    protected $table = 'observasi_harian';

    protected $fillable = [
        'siswa_id',
        'guru_id',
        'indikator_id',
        'kelas_id',
        'tanggal_observasi',
        'kategori_penilaian',
        'catatan_guru',
        'foto_dokumentasi'
    ];

    protected $casts = [
        'tanggal_observasi' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorCapaian::class, 'indikator_id');
    }
}
