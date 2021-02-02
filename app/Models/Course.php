<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function attrs()
    {
        return $this->belongsToMany('App\Models\Attr');
    }
    public function description()
    {
        return $this->belongsTo('App\Models\Description');
    }
    public function instructors()
    {
        return $this->belongsToMany('App\Models\Instructor');
    }
    public function meetsWith()
    {
        return $this->belongsToMany('App\Models\MeetsWith', 'course_meets_with', 'course_id', 'meets_with_id');
    }
    public function special()
    {
        return $this->belongsTo('App\Models\Special');
    }
    public function whenWhere()
    {
        return $this->belongsToMany('App\Models\WhenWhere', 'course_when_where', 'course_id', 'when_where_id');
    }
}
