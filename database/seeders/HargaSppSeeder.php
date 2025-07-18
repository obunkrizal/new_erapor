<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HargaSpp;
use App\Models\Periode;

class HargaSppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activePeriode = Periode::where('is_active', true)->first();
        
        if ($activePeriode) {
            // Sample SPP prices for different class levels
            $hargaSpp = [
                ['tingkat_kelas' => 'X', 'harga' => 500000],
                ['tingkat_kelas' => 'XI', 'harga' => 550000],
                ['tingkat_kelas' => 'XII', 'harga' => 600000],
            ];

            foreach ($hargaSpp as $harga) {
                HargaSpp::create([
                    'periode_id' => $activePeriode->id,
                    'tingkat_kelas' => $harga['tingkat_kelas'],
                    'harga' => $harga['harga'],
                    'keterangan' => 'Harga SPP untuk kelas ' . $harga['tingkat_kelas'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
