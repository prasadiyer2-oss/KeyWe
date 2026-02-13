<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $guarded = [];

    // 1. Budget (Belongs To - Single Choice)
    public function budgetRange()
    {
        return $this->belongsTo(BudgetRange::class);
    }

    // 2. Property Types (Belongs To Many - Multiple Choice)
    public function propertyTypes()
    {
        return $this->belongsToMany(PropertyType::class, 'property_type_user_preference');
    }

    // 3. BHK Types (Belongs To Many)
    public function bhkTypes()
    {
        return $this->belongsToMany(BhkType::class, 'bhk_type_user_preference');
    }

    // 4. Nearby Locations (Belongs To Many)
    public function nearbyLocations()
    {
        return $this->belongsToMany(NearbyLocation::class, 'nearby_location_user_preference');
    }

    // 5. Move In Timelines (Belongs To Many)
    public function moveInTimelines()
    {
        return $this->belongsToMany(MoveInTimeline::class, 'move_in_timeline_user_preference');
    }

    // 6. Localities (Belongs To Many)
    public function localities()
    {
        return $this->belongsToMany(Locality::class, 'locality_user_preference');
    }
}