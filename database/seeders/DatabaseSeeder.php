<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed application roles and a default super admin user
        $this->call([
            RolesSeeder::class,
            
        ]);
    }
}
