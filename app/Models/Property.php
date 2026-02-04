<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;

class Property extends Model
{
    use HasFactory, AsSource, Attachable;

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
