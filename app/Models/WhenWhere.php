<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhenWhere extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'when_where';
    public function courses()
    {
        return $this->belongsToMany('App\Models\Course', 'course_when_where', 'when_where_id', 'course_id');
    }
}
