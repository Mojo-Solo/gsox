<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestResult;

class TestsResultsAnswer extends Model
{

    protected $guarded = [];

    public function question(){
        return $this->belongsTo(Question::class);
    }

    public function option(){
        return $this->belongsTo(QuestionsOption::class);
    }

    public function testResult(){
        return $this->belongsTo(TestResult::class);
    }

}
