<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 1. Seed the Philippine Product Categories first
        $this->call([
            CategorySeeder::class,
            ProductCatalogSeeder::class,
        ]);

        // 2. Create the Master Admin Account
        User::updateOrCreate(
            ['email' => 'admin@tracker.com'],
            [
                'name' => 'Gerald Gahis',
                'password' => Hash::make('password'), 
                'is_admin' => true,
            ]
        );

        // 3. Create Standard Test Users
        User::updateOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name' => 'Maria Clara',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
    }
}
