<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User (Owner)
        User::updateOrCreate(
            ['email' => 'admin@laundry.test'],
            [
                'name' => 'Admin Laundry Kelompok 2',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'no_hp' => '081234567890',
            ]
        );

        // Kasir User
        User::updateOrCreate(
            ['email' => 'kasir@laundry.test'],
            [
                'name' => 'Kasir Salsabila',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'no_hp' => '089876543210',
            ]
        );

        $this->call([
            CustomerSeeder::class,
            ServiceSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
