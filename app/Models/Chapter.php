<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
//use Spatie\MediaLibrary\HasMedia\HasMedia;
use Illuminate\Support\Facades\File;
use Mtownsend\ReadTime\ReadTime;

class Chapter extends Model
{
    protected $table = 'chapters';
    protected $guarded = [];

    public function lessons()
    {
        return $this->belongsTo(Lesson::class);
    }
}
