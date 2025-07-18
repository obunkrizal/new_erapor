<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

        // Create sample teachers/staff
        $teachers = [
            ['name' => 'Ahmad Wijaya', 'email' => 'ahmad@school.com'],
            ['name' => 'Dewi Sartika', 'email' => 'dewi@school.com'],
            ['name' => 'Budi Santoso', 'email' => 'budi@school.com'],
            ['name' => 'Rina Kusuma', 'email' => 'rina@school.com'],
            ['name' => 'Joko Widodo', 'email' => 'joko@school.com'],
            ['name' => 'Maya Sari', 'email' => 'maya@school.com'],
            ['name' => 'Andi Pratama', 'email' => 'andi@school.com'],
            ['name' => 'Lestari Wulan', 'email' => 'lestari@school.com'],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra@school.com'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@school.com'],
            ['name' => 'Rizki Ramadhan', 'email' => 'rizki@school.com'],
            ['name' => 'Indah Permata', 'email' => 'indah@school.com'],
            ['name' => 'Agus Salim', 'email' => 'agus@school.com'],
            ['name' => 'Nurul Hidayah', 'email' => 'nurul@school.com'],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }

        $this->command->info('User seeder completed successfully!');
    }
}