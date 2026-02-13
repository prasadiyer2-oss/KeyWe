<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 1. Single Selection Fields (Foreign Keys)
            'budget_range_id' => 'required|exists:budget_ranges,id',
            
            // 2. Multiple Selection Fields (Arrays of IDs)
            'property_type_ids'   => 'required|array',
            'property_type_ids.*' => 'exists:property_types,id', // Check each ID exists

            'bhk_type_ids'        => 'required|array',
            'bhk_type_ids.*'      => 'exists:bhk_types,id',

            'move_in_timeline_ids'=> 'required|array',
            'move_in_timeline_ids.*' => 'exists:move_in_timelines,id',

            'nearby_location_ids' => 'nullable|array',
            'nearby_location_ids.*' => 'exists:nearby_locations,id',

            'locality_ids'        => 'nullable|array',
            'locality_ids.*'      => 'exists:localities,id',
            
            // 3. Optional: Search radius if you kept it
            'search_radius_km'    => 'nullable|integer|min:1|max:50',
        ];
    }
}