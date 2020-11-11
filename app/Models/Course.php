<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function attributes()
    {
        return $this->belongsToMany('App\Models\Attribute');
    }
    public function instructors()
    {
        return $this->belongsToMany('App\Models\Instructor');
    }
}
