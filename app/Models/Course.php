<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    public function attrs()
    {
        return $this->hasMany('App\Models\Attribute');
    }
    public function instructors()
    {
        return $this->hasMany('App\Models\Instructor');
    }
}
