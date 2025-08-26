<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $periodes = [

            [
                'nama_periode' => 'Semester Ganjil 2024/2025',
                'tahun_ajaran' => '2024/2025',
                'semester' => 'ganjil',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_periode' => 'Semester Genap 2024/2025',
                'tahun_ajaran' => '2024/2025',
                'semester' => 'genap',
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_periode' => 'Semester Ganjil 2025/2026',
                'tahun_ajaran' => '2025/2026',
                'semester' => 'ganjil',
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('periodes')->insert($periodes);
    }
}
