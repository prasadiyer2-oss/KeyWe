<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;

class Property extends Model
{
    use HasFactory, AsSource, Attachable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'partner_id',  
        'project_id',      // Renamed from project_id
        'title',
        'description',       // New column
        'price',
        'carpet_area',       // Renamed from area_sqft
        'bhk',               // New column
        'property_type',     // New column
        'location',          // New column
        'handover_date',     // New column
        'financing_option',  // New column
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'handover_date' => 'date',
        'price' => 'integer',
        'carpet_area' => 'integer',
        'bhk' => 'integer',
    ];

    /**
     * Relationship: A property belongs to a specific Partner.
     * Note: Ensure you have a Partner model, or point this to User::class if partners are Users.
     */
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id'); 
        // If you have a dedicated 'Partner' model, change User::class to Partner::class
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}