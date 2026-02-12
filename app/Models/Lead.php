<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    
    protected $fillable = ['project_id', 'name', 'email', 'phone', 'status', 'message'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
