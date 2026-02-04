<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Property extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'project_id',
        'title',
        'configuration',
        'area_sqft',
        'price',
        'status',
    ];

    /**
     * Relationship: A property belongs to a specific Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
