<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetRange extends Model
{
    // Allow mass assignment for seeding
    protected $guarded = [];

    // Optional: If you want to see which users picked this budget
    public function userPreferences()
    {
        return $this->hasMany(UserPreference::class);
    }
}