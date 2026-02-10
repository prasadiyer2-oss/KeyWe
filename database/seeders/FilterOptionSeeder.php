<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filter;
use App\Models\FilterOption;

class FilterOptionSeeder extends Seeder
{
    public function run()
    {
        // 1. PRICE (slug: price)
        $this->seedOptions('Price', 'price', [
            ['label' => 'Under ₹50 Lakhs',    'value' => '0-5000000'],
            ['label' => '₹50 L - ₹1 Cr',      'value' => '5000000-10000000'],
            ['label' => '₹1 Cr - ₹1.5 Cr',    'value' => '10000000-15000000'],
            ['label' => '₹1.5 Cr - ₹2 Cr',    'value' => '15000000-20000000'],
            ['label' => '₹2 Cr - ₹3 Cr',      'value' => '20000000-30000000'],
            ['label' => '₹3 Cr - ₹5 Cr',      'value' => '30000000-50000000'],
            ['label' => 'Above ₹5 Cr',        'value' => '50000000-9999999999'],
        ]);

        // 2. CARPET AREA (slug: carpet-area)
        $this->seedOptions('Carpet Area', 'carpet-area', [
            ['label' => 'Under 500 sqft',     'value' => '0-500'],
            ['label' => '500 - 800 sqft',     'value' => '500-800'],
            ['label' => '800 - 1200 sqft',    'value' => '800-1200'],
            ['label' => '1200 - 1800 sqft',   'value' => '1200-1800'],
            ['label' => '1800 - 2500 sqft',   'value' => '1800-2500'],
            ['label' => 'Above 2500 sqft',    'value' => '2500-99999'],
        ]);

        // 3. FLOOR (slug: floor)
        $this->seedOptions('Floor', 'floor', [
            ['label' => 'Ground / Low Rise (0-4)',  'value' => '0-4'],
            ['label' => 'Mid Rise (5-10)',          'value' => '5-10'],
            ['label' => 'High Rise (11-20)',        'value' => '11-20'],
            ['label' => 'Sky High (21+)',           'value' => '21-200'],
        ]);

        // 4. FINANCING (slug: financing)
        $this->seedOptions('Financing', 'financing', [
            ['label' => 'Loan Available',      'value' => 'Loan'],
            ['label' => 'Full Payment Only',   'value' => 'Full Payment'],
            ['label' => 'Both Options',        'value' => 'Both'],
        ]);

        // 5. BHK (slug: bhk)
        $this->seedOptions('BHK', 'bhk', [
            ['label' => '1 BHK',   'value' => '1'],
            ['label' => '1.5 BHK', 'value' => '1.5'],
            ['label' => '2 BHK',   'value' => '2'],
            ['label' => '2.5 BHK', 'value' => '2.5'],
            ['label' => '3 BHK',   'value' => '3'],
            ['label' => '3.5 BHK', 'value' => '3.5'],
            ['label' => '4 BHK',   'value' => '4'],
            ['label' => '4+ BHK',  'value' => '4+'],
            ['label' => 'Studio',  'value' => 'Studio'],
        ]);

        // 6. PROPERTY TYPE (slug: property-type)
        $this->seedOptions('Property Type', 'property-type', [
            ['label' => 'Apartment', 'value' => 'Apartment'],
            ['label' => 'Villa',     'value' => 'Villa'],
            ['label' => 'Plot',      'value' => 'Plot'],
            ['label' => 'Studio',    'value' => 'Studio'],
            ['label' => 'Penthouse', 'value' => 'Penthouse'],
        ]);

        // 7. CONSTRUCTION STATUS (slug: construction-status)
        $this->seedOptions('Construction Status', 'construction-status', [
            ['label' => 'Ready to Move',      'value' => 'Ready to Move'],
            ['label' => 'Under Construction', 'value' => 'Under Construction'],
            ['label' => 'New Launch',         'value' => 'New Launch'],
        ]);

        // 8. POSSESSION DATE (slug: possession-date)
        $this->seedOptions('Possession Date', 'possession-date', [
            ['label' => 'Immediate',        'value' => '0'],
            ['label' => 'Within 3 Months',  'value' => '3'],
            ['label' => 'Within 6 Months',  'value' => '6'],
            ['label' => 'Within 1 Year',    'value' => '12'],
            ['label' => 'Within 2 Years',   'value' => '24'],
            ['label' => 'Long Term (2+)',   'value' => '25+'],
        ]);

        // NOTE: Location is skipped intentionally as per request.
    }

    /**
     * Helper to create options for a specific filter slug
     */
    private function seedOptions($name, $slug, $options)
    {
        // 1. Find the Filter by Slug
        $filter = Filter::where('slug', $slug)->first();

        // If filter doesn't exist (maybe deleted manually?), recreate it
        if (!$filter) {
            $filter = Filter::create([
                'name' => $name, 
                'slug' => $slug, 
                'type' => 'select'
            ]);
        }

        $this->command->info("Seeding Options for: $name ($slug)");

        // 2. Create Options
        foreach ($options as $opt) {
            FilterOption::firstOrCreate(
                [
                    'filter_id' => $filter->id,
                    'value'     => $opt['value']
                ],
                [
                    'label'     => $opt['label']
                ]
            );
        }
    }
}