<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class FilterOption extends Model
{
    use HasFactory, AsSource;

    protected $fillable = ['filter_id', 'label', 'value'];

    /**
     * Relationship: An Option belongs to one Filter category.
     */
    public function filter()
    {
        return $this->belongsTo(Filter::class);
    }

    /**
     * Relationship: An Option belongs to many Projects.
     * This uses the 'project_filter_option' pivot table.
     */
    public function properties()
    {
        return $this->belongsToMany(
            Property::class,
            'property_filter_option',
            'filter_option_id',
            'property_id'
        );
    }
}