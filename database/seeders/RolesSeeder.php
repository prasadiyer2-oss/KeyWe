<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate([
            'slug' => 'super_admin',
        ], [
            'name' => 'Super Admin',
            'permissions' => ['*' => true],
        ]);

        Role::firstOrCreate([
            'slug' => 'builder',
        ], [
            'name' => 'Builder',
            'permissions' => [],
        ]);
    }
}
