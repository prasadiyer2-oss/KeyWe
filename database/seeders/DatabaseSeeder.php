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
        // 1. Roles & Permissions (Standard)
        $this->call([
            RolesSeeder::class,
        ]);

        // 2. Generate Filters & Options from Properties (Dynamic)
        $this->call([
            PropertyFilterSeeder::class,
        ]);

        $this->call([
            FilterOptionSeeder::class,
        ]);
    }
}