<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Filter extends Model
{
    use HasFactory, AsSource;

    protected $fillable = ['name', 'slug', 'type'];

    /**
     * Relationship: A Filter has many specific Options.
     * e.g. "Budget" has "1Cr-2Cr", "2Cr-3Cr"
     */
    public function options()
    {
        return $this->hasMany(FilterOption::class);
    }
}