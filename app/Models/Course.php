<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    public function pastcourses()
    {
        return $this->hasMany('App\Models\PastCourse');
    }
    public function geneds()
    {
        return $this->belongsToMany('App\Models\GenEd');
    }
}
