<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravolt\Indonesia\Seeds\CitiesSeeder;
use Laravolt\Indonesia\Seeds\VillagesSeeder;
use Laravolt\Indonesia\Seeds\DistrictsSeeder;
use Laravolt\Indonesia\Seeds\ProvincesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'admin',
            'password' => bcrypt('admin@123'),
            'role' => 'admin',
        ]);
        $this->call([
            ProvincesSeeder::class,
            CitiesSeeder::class,
            DistrictsSeeder::class,
            VillagesSeeder::class,
            SiswaSeeder::class,
            GuruSeeder::class,
            PeriodeSeeder::class,
            KelasSeeder::class,
            SuratKeputusanGuruSeeder::class,
            PaudDimensiIndikatorSeeder::class,
            PaudEkstrakurikulerSeeder::class, // PaudEkstrakurikulerSeeder::class,
            PaudObservasiHarian::class,
        ]);
    }
}
