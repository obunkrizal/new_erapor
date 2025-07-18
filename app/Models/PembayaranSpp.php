<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_spps';

    protected $fillable = [
        'siswa_id',
        'periode_id',
        'kelas_id',
        'month',
        'no_inv',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'catatan'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(\App\Models\Guru::class);
    }
}
