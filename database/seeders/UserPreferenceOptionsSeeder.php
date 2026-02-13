<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPreferenceOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. BHK Types (From Image 4)
        // ==========================================
        $bhkTypes = [
            ['id' => 1, 'label' => '1 BHK', 'sort_order' => 1],
            ['id' => 2, 'label' => '2 BHK', 'sort_order' => 2],
            ['id' => 3, 'label' => '3 BHK', 'sort_order' => 3],
            ['id' => 4, 'label' => 'Villa / Plot', 'sort_order' => 4],
        ];
        
        foreach ($bhkTypes as $type) {
            DB::table('bhk_types')->upsert($type, ['id'], ['label', 'sort_order']);
        }

        // ==========================================
        // 2. Move-In Timeline (From Image 4)
        // ==========================================
        $timelines = [
            ['id' => 1, 'label' => 'Immediately', 'sort_order' => 1],
            ['id' => 2, 'label' => '3-6 months', 'sort_order' => 2],
            ['id' => 3, 'label' => '6-12 months', 'sort_order' => 3],
            ['id' => 4, 'label' => 'Just exploring', 'sort_order' => 4],
        ];

        foreach ($timelines as $timeline) {
            DB::table('move_in_timelines')->upsert($timeline, ['id'], ['label', 'sort_order']);
        }

        // ==========================================
        // 3. Nearby Locations (From Image 3)
        // ==========================================
        $nearby = [
            ['id' => 1, 'label' => 'Near work', 'icon' => 'briefcase'],
            ['id' => 2, 'label' => 'Near school', 'icon' => 'school'],
            ['id' => 3, 'label' => 'Near metro', 'icon' => 'train'],
            ['id' => 4, 'label' => 'Quiet area', 'icon' => 'tree'],
            ['id' => 5, 'label' => 'Investment hotspot', 'icon' => 'trending-up'],
        ];

        foreach ($nearby as $loc) {
            DB::table('nearby_locations')->upsert($loc, ['id'], ['label', 'icon']);
        }

        // ==========================================
        // 4. Budget Ranges (From Image 2)
        // ==========================================
        $budgets = [
            ['id' => 1, 'label' => 'Under ₹50L', 'min_price' => 0, 'max_price' => 5000000, 'sort_order' => 1],
            ['id' => 2, 'label' => '₹50L - ₹1Cr', 'min_price' => 5000000, 'max_price' => 10000000, 'sort_order' => 2],
            ['id' => 3, 'label' => '₹1Cr - ₹2Cr', 'min_price' => 10000000, 'max_price' => 20000000, 'sort_order' => 3],
            ['id' => 4, 'label' => '₹2Cr+', 'min_price' => 20000000, 'max_price' => 9999999999, 'sort_order' => 4],
        ];

        foreach ($budgets as $budget) {
            DB::table('budget_ranges')->upsert($budget, ['id'], ['label', 'min_price', 'max_price', 'sort_order']);
        }

        // ==========================================
        // 5. Localities (From Image 3 - Search List)
        // ==========================================
        $localities = [
            ['id' => 1, 'name' => 'Indiranagar', 'city' => 'Bangalore'],
            ['id' => 2, 'name' => 'HSR Layout', 'city' => 'Bangalore'],
            ['id' => 3, 'name' => 'JP Nagar', 'city' => 'Bangalore'],
            ['id' => 4, 'name' => 'Whitefield', 'city' => 'Bangalore'],
            ['id' => 5, 'name' => 'Koramangala', 'city' => 'Bangalore'],
        ];

        foreach ($localities as $area) {
            DB::table('localities')->upsert($area, ['id'], ['name', 'city']);
        }
        
        // ==========================================
        // 6. Property Types (From Image 2 - Tabs)
        // ==========================================
        // Note: Image shows "New Projects", "Resale", "Rentals". 
        // We will map these to standard database types.
        $propTypes = [
            ['id' => 1, 'name' => 'New Projects', 'slug' => 'new-projects', 'is_active' => true],
            ['id' => 2, 'name' => 'Resale', 'slug' => 'resale', 'is_active' => true],
            ['id' => 3, 'name' => 'Rentals', 'slug' => 'rentals', 'is_active' => true],
        ];

        foreach ($propTypes as $ptype) {
            DB::table('property_types')->upsert($ptype, ['id'], ['name', 'slug', 'is_active']);
        }
    }
}