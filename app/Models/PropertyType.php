<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    // Allow mass assignment
    protected $guarded = [];

    /**
     * Relationship: Get all user preferences that selected this property type.
     * This uses the pivot table 'property_type_user_preference'.
     */
    public function userPreferences()
    {
        return $this->belongsToMany(UserPreference::class, 'property_type_user_preference');
    }
}