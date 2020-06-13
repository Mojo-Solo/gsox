<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Course;
class CourseProgress extends Model
{
    protected $table = "course_progress";
    protected $guarded = [];

    public function model()
    {
        return $this->morphTo();
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }




}
