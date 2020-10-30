<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;
    public function pastcourses()
    {
        return $this->belongsToMany('App\Models\PastCourse');
    }
}
