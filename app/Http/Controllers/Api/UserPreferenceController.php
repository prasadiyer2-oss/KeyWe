<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserPreferenceRequest;
use App\Models\BhkType;
use App\Models\BudgetRange;
use App\Models\Locality;
use App\Models\MoveInTimeline;
use App\Models\NearbyLocation;
use App\Models\PropertyType;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class UserPreferenceController extends Controller
{
    /**
     * Store or Update User Preferences
     */
    public function getOptions()
    {
        return response()->json([
            'bhk_types' => BhkType::orderBy('sort_order', 'asc')
                ->get(['id', 'label']),

            'budget_ranges' => BudgetRange::orderBy('sort_order', 'asc')
                ->get(['id', 'label']),

            'property_types' => PropertyType::where('is_active', true)
                ->get(['id', 'name', 'slug']),

            'move_in_timelines' => MoveInTimeline::orderBy('sort_order', 'asc')
                ->get(['id', 'label']),

            'nearby_locations' => NearbyLocation::get(['id', 'label', 'icon']),

            'localities' => Locality::orderBy('name', 'asc')
                ->get(['id', 'name', 'city']),
        ]);
    }

    public function store(StoreUserPreferenceRequest $request)
    {
        $user = $request->user();

        // Transaction ensures everything is saved, or nothing is saved (prevents partial data)
        DB::transaction(function () use ($user, $request) {
            
            // 1. Create or Update the Main Record
            // We use updateOrCreate so if the user comes back, we update their existing pref
            $preference = UserPreference::updateOrCreate(
                ['user_id' => $user->id], // Find by User ID
                [
                    'budget_range_id'  => $request->budget_range_id,
                    'search_radius_km' => $request->search_radius_km ?? 10,
                ]
            );

            // 2. Sync Pivot Tables (The "Many-to-Many" Magic)
            // sync() will automatically add new connections and remove unselected ones.
            
            $preference->propertyTypes()->sync($request->property_type_ids);
            $preference->bhkTypes()->sync($request->bhk_type_ids);
            $preference->moveInTimelines()->sync($request->move_in_timeline_ids);
            
            // Handle optional arrays
            if ($request->has('nearby_location_ids')) {
                $preference->nearbyLocations()->sync($request->nearby_location_ids);
            }
            
            if ($request->has('locality_ids')) {
                $preference->localities()->sync($request->locality_ids);
            }
        });

        return response()->json([
            'message' => 'Preferences saved successfully!',
            'status' => 'success'
        ], 200);
    }

    /**
     * Get Current Preferences (For when user opens the screen again)
     */
    public function show()
    {
        $user = auth()->user();
        
        $preference = UserPreference::where('user_id', $user->id)
            ->with([
                'budgetRange', 
                'propertyTypes', 
                'bhkTypes', 
                'moveInTimelines', 
                'nearbyLocations', 
                'localities'
            ])
            ->first();

        if (!$preference) {
            return response()->json(['message' => 'No preferences found'], 404);
        }

        return response()->json($preference);
    }
}