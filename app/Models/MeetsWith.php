<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetsWith extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'meets_with';
    public function courses()
    {
        return $this->belongsToMany('App\Models\Course', 'course_meets_with', 'meets_with_id', 'course_id');
    }
}
