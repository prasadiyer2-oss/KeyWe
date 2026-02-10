<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Filter;
use App\Models\FilterOption;
use Illuminate\Support\Str;

class PropertyFilterSeeder extends Seeder
{
    public function run()
    {
        // 1. Define mappings: 'Display Name' => 'Database Column'
        $mappings = [
            'BHK'                 => 'bhk',
            'Property Type'       => 'property_type',
            'Location'            => 'location',
            'Construction Status' => 'construction_status',
            'Possession Date'     => 'possession_date', 
            'Financing'           => 'financing_option',
            'Floor'               => 'floor_number',
            'Carpet Area'         => 'carpet_area',
            'Price'               => 'price',
        ];

        foreach ($mappings as $filterName => $column) {
            
            // A. Create the Parent Filter
            $filter = Filter::firstOrCreate(
                ['slug' => Str::slug($filterName)],
                ['name' => $filterName, 'type' => 'select']
            );

            $this->command->info("Processing Filter: $filterName...");

            // B. Fetch properties that have a value for this column
            $properties = Property::whereNotNull($column)->get();

            foreach ($properties as $property) {
                $rawValue = $property->{$column};

                // Clean up string values
                if (is_string($rawValue)) {
                    $rawValue = trim($rawValue);
                }
                
                // Skip empty values
                if (empty($rawValue)) continue;

                // C. Format the Label for better UI (Price, Area, Floor)
                $label = ucfirst($rawValue);

                if ($column === 'price') {
                    $label = $this->formatPrice($rawValue);
                } elseif ($column === 'carpet_area') {
                    $label = $rawValue . ' sqft';
                } elseif ($column === 'floor_number') {
                    $label = $this->ordinal($rawValue) . ' Floor';
                }

                // D. Create the Option
                $option = FilterOption::firstOrCreate(
                    [
                        'filter_id' => $filter->id,
                        'value'     => $rawValue, // Store raw value (e.g., 15000000) for querying
                    ],
                    [
                        'label'     => $label // Store formatted label (e.g., ₹ 1.5 Cr) for display
                    ]
                );

                // E. Link Property to Option
                // syncWithoutDetaching ensures we append, not overwrite
                $property->filterOptions()->syncWithoutDetaching([$option->id]);
            }
        }
        
        $this->command->info('All filters and options seeded successfully!');
    }

    /**
     * Helper: Format Floor Numbers (1 -> 1st, 2 -> 2nd)
     */
    private function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
        return $number. $ends[$number % 10];
    }

    /**
     * Helper: Format Indian Currency (Lakhs/Crores)
     */
    private function formatPrice($price) {
        if ($price >= 10000000) {
            return '₹ ' . round($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            return '₹ ' . round($price / 100000, 2) . ' L';
        }
        return '₹ ' . number_format($price);
    }
}