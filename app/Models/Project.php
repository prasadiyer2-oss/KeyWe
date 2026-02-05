<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable; // Required for Orchid

class Project extends Model
{
    use HasFactory, AsSource, Attachable;

    protected $fillable = [
        'name', 'location', 'rera_number', 'project_type', 
        'status', 'verification_status', 'total_units', 'user_id'
    ];

    /**
     * Relation: A project belongs to a Builder (User)
     */
    public function builder()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation: A project has many filter options (e.g., 2BHK, 3BHK, Budget A)
     */
    public function filterOptions()
    {
        return $this->belongsToMany(FilterOption::class, 'project_filter_option');
    }

    public function scopeByBuilder($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'Verified');
    }
}