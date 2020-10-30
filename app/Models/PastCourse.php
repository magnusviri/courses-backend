<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PastCourse extends Model
{
    use HasFactory;
    public function courses()
    {
        return $this->belongsToMany('App\Models\Course');
    }
    public function instructors()
    {
        return $this->belongsToMany('App\Models\Instructor');
    }
    public function year()
    {
        return $this->belongsTo('App\Models\Year');
    }
    public function semester()
    {
        return $this->belongsTo('App\Models\Semester');
    }
    public function syllabi()
    {
        return $this->belongsTo('App\Models\Syllabus');
    }
}
