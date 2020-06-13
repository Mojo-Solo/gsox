<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestsResult extends Model
{

    protected $guarded = [];

    public function answers()
    {
        return $this->hasMany('App\Models\TestsResultsAnswer');
    }

    public function test(){
        return $this->belongsTo(Test::class);
    }
}
