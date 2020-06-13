<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use App\Models\QuestionsOption;
use Session;
/**
 * Class Question
 *
 * @package App
 * @property text $question
 * @property string $question_image
 * @property integer $score
 */
class Question extends Model
{
    use SoftDeletes;

    // protected $guarded = [];
    protected $fillable = ['question', 'question_image', 'score','user_id','review_topic_id'];

    protected static function boot()
    {
        parent::boot();
        if (auth()->check()) {
            if (auth()->user()->hasRole('Supervisor')) {
                static::addGlobalScope('filter', function (Builder $builder) {
                    $courses = auth()->user()->courses->pluck('id');
                    $builder->whereHas('tests', function ($q) use ($courses) {
                        $q->whereIn('tests.course_id', $courses);
                    });
                });
            }
        }

        static::deleting(function ($question) { // before delete() method call this
            if ($question->isForceDeleting()) {
                if (File::exists(public_path('/storage/uploads/' . $question->question_image))) {
                    File::delete(public_path('/storage/uploads/' . $question->question_image));
                }
            }
        });

    }

    /**
     * Set attribute to money format
     * @param $input
     */
    public function setScoreAttribute($input)
    {
        $this->attributes['score'] = $input ? $input : null;
    }

    public function randomoptions()
    {
        if(config('option_randomizer')) {
            return QuestionsOption::where('question_id',$this->id)->orderBy(\DB::raw('RAND()'))->get();
        }
        return QuestionsOption::where('question_id',$this->id)->orderBy('option_text','asc')->get();
    }
    public function qes_options()
    {
        if(Session::has('question_id') && Session::get('question_id')==$this->id && Session::has('qes_orders') && !empty(Session::get('qes_orders'))) {
            $orders=explode(",", Session::get('qes_orders'));
            Session::forget('question_id');
            Session::forget('qes_orders');
            Session::save();
            $options=array();
            for($i=0; $i<count($orders); $i++) {
                $option=QuestionsOption::find($orders[$i]);
                array_push($options, $option);
            }
            return $options;
        }
        return QuestionsOption::where('question_id',$this->id)->orderBy('option_text','asc')->get();
    }
    public function options()
    {
        return $this->hasMany('App\Models\QuestionsOption');
    }
    public function isCompleted()
    {
        return null;
    }

    public function isAttempted($result_id){
        $result = TestsResultsAnswer::where('tests_result_id', '=', $result_id)
            ->where('question_id', '=', $this->id)
            ->first();
        if($result != null){
            return true;
        }
        return false;
    }
    
    public function questions(){
        return $this->hasOne('App\Models\Question','id');
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'question_test');
    }


}
