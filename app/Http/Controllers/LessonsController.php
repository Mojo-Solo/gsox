<?php

namespace App\Http\Controllers;

use App\Helpers\Auth\Auth;
use App\Models\Topic;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Question;
use App\Models\QuestionsOption;
use App\Models\Course;
use App\Models\Test;
use App\Models\CourseTimeline;
use App\Models\TestsResult;
use App\Models\ChapterStudent;
use App\Models\VideoProgress;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Session;
class LessonsController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    public function show($course_id='', $lesson_slug='', $type='', $token='')
    {
        $test_result = "";
        $completed_lessons = "";
        $purchased_course = "";
        $lessons = "";
        $test_exists = "";
        $item_type ="";
        $sequence='';
        $question_id='';
        $lesson='';
        $model="";
        $quest=0;
        $test_exists = FALSE;
        $inline_test_exists = FALSE;
        $inline_test = NULL;
        $test_status  = NULL;
        $quiz_id = 0;
        $disable_next_btn = false;
        if(isset($_GET['seq']) && !empty($_GET['seq'])) {
            $sequence=$_GET['seq'];
        }
        $course=Course::findOrfail($course_id);
        if($sequence) {
            $model=CourseTimeline::where('course_id',$course_id)->where('sequence',$sequence)->firstOrFail();
            $type=$model->model_type;
            if($type=="App\Models\Question"){
                $item_type="question";
                $quest=1;
                $lesson=Question::where('id',$model->model_id)->first();
            }
            if($type=="App\Models\Topic"){
                $item_type="topic";
                $quest=0;
                $lesson=Topic::where('id',$model->model_id)->first();
            }if($type=="App\Models\Lesson"){
                $item_type="lesson";
                $quest=0;
                $lesson=Lesson::where('id',$model->model_id)->first();
            }
        }

        if(empty($lesson)) {
            if(filter_var($type, FILTER_VALIDATE_INT) === false || empty($type) || empty($token)){
                $lesson = Topic::where('slug', $lesson_slug)->where('course_id', $course_id)->where('published', '=', 1)->orderBy('id','desc')->first();
            } else{
                $lesson = Topic::where('id', $type)->where('course_id', $course_id)->where('published', '=', 1)->orderBy('id','desc')->first();
            }
            if(empty($lesson)) {
                $lesson = Topic::where('slug', $lesson_slug)->where('published', '=', 1)->orderBy('id','desc')->first();
            }
        }

        if (empty($lesson)){
            if(filter_var($type, FILTER_VALIDATE_INT) === false || empty($type) || empty($token)){
                $lesson = Lesson::where('slug', $lesson_slug)->where('course_id', $course_id)->where('published', '=', 1)->first();
            } else {
                $lesson = Lesson::where('id', $type)->where('course_id', $course_id)->where('published', '=', 1)->first();
            }
            // if (!empty($lesson) && !ChapterStudent::where('model_id',$lesson->id)->where('course_id',$course->id)->where('user_id', \Auth::id())->count()) {
            //     ChapterStudent::create([
            //         'model_type' => "App\Models\Lesson",
            //         'model_id' => $lesson->id,
            //         'user_id' => auth()->user()->id,
            //         'course_id' => $course->id
            //     ]);
            // }
        }
        if (empty($lesson)) {
            if(filter_var($type, FILTER_VALIDATE_INT) === false || empty($type) || empty($token)){
                $lesson = Topic::where('slug', $lesson_slug)->where('course_id', $course_id)->where('published', '=', 1)->orderBy('id','desc')->first();
            } 
            if(empty($lesson)){
                $lesson = Topic::where('id', $type)->where('course_id', $course_id)->where('published', '=', 1)->orderBy('id','desc')->first();
            }
        }
        
        if (empty($lesson)) {
            if(filter_var($type, FILTER_VALIDATE_INT) === false || empty($type) || empty($token)){
                $lesson = Test::where('slug', $lesson_slug)->where('course_id', $course_id)->where('published', '=', 1)->first();
                if(empty($lesson)) {
                    $lesson = Test::where('slug', $lesson_slug)->where('published', '=', 1)->first();
                    if(!empty($lesson) && empty($lesson->course_id)) {
                        $lesson->course_id=$course_id;
                        $lesson->save();
                    }
                }
            } else {
                $lesson = Test::where('id', $type)->where('published', '=', 1)->first();
            }
            $test_result = NULL;
            if ($lesson) {
                $lesson->full_text = $lesson->description;
                $test_result = TestsResult::where('test_id', $lesson->id)->where('user_id', \Auth::id())->first();
            }
        }
        if (empty($lesson)) {
            $quest=1;
            if(filter_var($type, FILTER_VALIDATE_INT) === false || empty($type) || empty($token)){
                $lesson = Question::find($lesson_slug);
            } else {
                $lesson = Question::find($lesson_slug);
            }
        }

        if ((int)config('lesson_timer') == 0 && $type !='topic' && $quest==0) {
            // if (!empty($lesson) && $lesson->chapterStudents()->where('user_id', \Auth::user()->id)->count() == 0) {
            //     $lesson->chapterStudents()->create([
            //         'model_type' => get_class($lesson),
            //         'model_id' => $lesson->id,
            //         'user_id' => \Auth::user()->id,
            //         'course_id' => $course->id
            //     ]);
            // }
        }
        if(!empty($lesson)) {
            $timeline=CourseTimeline::where('course_id',$course_id)->where('model_id',$lesson->id)->where('model_type',get_class($lesson))->orderBy('sequence', 'asc')->first();
            if($timeline) {
                $previous_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence','<',(int)$timeline->sequence)->orderby('sequence', 'desc')->first();
                $next_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence','>', (int)$timeline->sequence)->orderby('sequence', 'asc')->first();

            }

            if(empty($previous_lesson) && empty($next_lesson)){
                $previous_lesson = $course->courseTimeline()->orderBy('sequence', 'desc')->first();
                if(empty($sequence)) { 
                    $next_lesson = $course->courseTimeline()->orderBy('sequence', 'asc')->first();
                } else {
                    $next_lesson = $course->courseTimeline()->where('sequence', '>', $sequence)->orderBy('sequence', 'asc')->first();
                }
            }

            $lessons = CourseTimeline::where('course_id',$course_id)->orderby('sequence', 'asc')->get();
            $purchased_course = $course->students()->where('user_id', \Auth::id())->count() > 0;
            
            $test_exists = FALSE;
            $inline_test_exists = FALSE;
            $inline_test = NULL;
            $test_status  = NULL;
            $quiz_id = 0;
            $disable_next_btn = false;

            if(get_class($lesson) == 'App\Models\Topic'){
                $item_type = 'topic';
                $quiz_id = $lesson->quiz_id;
            }

            if(get_class($lesson) == 'App\Models\Lesson'){
                $item_type = 'lesson';
            }

            if(get_class($lesson) == 'App\Models\Test'){
                $item_type = 'test';
                $test_exists = TRUE;
                $quiz_id = $lesson->id;
            }

            if(get_class($lesson) == 'App\Models\Question'){
                $item_type = 'question';
                $test_exists = TRUE;
                $quiz_id = $lesson->id;
            }
            if($item_type == 'topic'){
                // if (!ChapterStudent::where('model_id',$lesson->id)->where('course_id',$course->id)->where('user_id', \Auth::id())->count()) {
                //     ChapterStudent::create([
                //         'model_type' => "App\Models\Topic",
                //         'model_id' => $lesson->id,
                //         'user_id' => auth()->user()->id,
                //         'course_id' => $course->id
                //     ]);
                // }
            }
            if($item_type == 'topic' || $item_type == 'test'){
                if($quiz_id > 0){
                    $test_info = Test::find($quiz_id);
                    $test_result = TestsResult::where('test_id', $quiz_id)->where('user_id', \Auth::id())->first();
                    if($test_result){
                        foreach($test_info->questions as $question){
                            if(!$question->isAttempted($test_result->id)){
                                $test_status = 'failed';
                            }
                            foreach ($question->options as $option){
                                if($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2){
                                    $test_status = 'failed';
                                    break;
                                }else{
                                    $test_status = 'passed';
                                }
                            }
                            if($test_status == 'failed'){
                                break;
                            }
                        }
                    }
                } 
            }
            if($item_type == 'question'){
                if($quiz_id > 0){
                    $test_result = TestsResult::where('question_id', $quiz_id)->where('user_id', \Auth::id())->first();
                    if($test_result){
                        if(!$lesson->isAttempted($test_result->id)){
                            $test_status = 'failed';
                        }
                        foreach ($lesson->options as $option){
                            if($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2){
                                $test_status = 'failed';
                                break;
                            }else{
                                $test_status = 'passed';
                                $question_id=$quiz_id;
                                if (!ChapterStudent::where('model_id',$quiz_id)->where('course_id',$course->id)->where('user_id', \Auth::id())->count()) {
                                    ChapterStudent::create([
                                        'model_type' => "App\Models\Question",
                                        'model_id' => $quiz_id,
                                        'user_id' => auth()->user()->id,
                                        'course_id' => $course->id
                                    ]);
                                }
                            }
                        }
                    }
                } 
            }

            if($type =='topic' &&  !$inline_test_exists){
                if (ChapterStudent::where('model_id',$lesson->id)->where('user_id', \Auth::id())->count() == 0) {
                    ChapterStudent::create([
                        'model_type' => get_class($lesson),
                        'model_id' => $lesson->id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $course_id
                    ]);
                }  
            }
            if(($inline_test_exists && $test_status !='passed') || ($test_exists && $test_status !='passed')){
                $disable_next_btn = true;
            }
            // if ($item_type!=="question" && !empty($lesson) && $lesson->chapterStudents()->where('user_id', \Auth::user()->id)->count() == 0) {
            //     $lesson->chapterStudents()->create([
            //         'model_type' => get_class($lesson),
            //         'model_id' => $lesson->id,
            //         'user_id' => \Auth::user()->id,
            //         'course_id' => $course->id
            //     ]);
            // }
    } elseif(!empty($model)) {
        $previous_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence', (int)$model->sequence-1)->first();
        $next_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence', (int)$model->sequence+1)->first();
    }elseif(CourseTimeline::where('course_id',$course_id)->where('model_id',$lesson_slug)->count()) {
        $model=CourseTimeline::where('course_id',$course_id)->where('model_id',$lesson_slug)->firstOrFail();
        $previous_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence', (int)$model->sequence-1)->first();
        $next_lesson = CourseTimeline::where('course_id',$course_id)->where('sequence', (int)$model->sequence+1)->first();
    } else {
        show_404();
    }
    if($previous_lesson) {
        if (!ChapterStudent::where('model_id',$previous_lesson->model_id)->where('course_id',$course->id)->where('user_id', \Auth::id())->count()) {
            ChapterStudent::create([
                'model_type' => $previous_lesson->model_type,
                'model_id' => $previous_lesson->model_id,
                'user_id' => \Auth::user()->id,
                'course_id' => $course->id
            ]);
        }
    }
    if(!empty($lesson) && $lesson->id==CourseTimeline::where('course_id',$course->id)->orderBy('sequence','DESC')->first()->model_id) {
        if (!ChapterStudent::where('model_id',$lesson->id)->where('course_id',$course->id)->where('user_id', \Auth::id())->count()) {
            ChapterStudent::create([
                'model_type' => get_class($lesson),
                'model_id' => $lesson->id,
                'user_id' => \Auth::user()->id,
                'course_id' => $course->id
            ]);
        }
    }
        $completed_lessons = \Auth::user()->chapters()->where('course_id', $course_id)->get()->pluck('model_id')->toArray();

    if(!empty($lesson)) {
        CourseProgress::where('course_id',$course->id)->where('user_id',\Auth::user()->id)->delete();
        CourseProgress::create(['model_type' => get_class($lesson),'model_id' => $lesson->id,'user_id' => \Auth::user()->id,'course_id' => $course->id]);
    }

    return view($this->path . '.courses.lesson', compact('course','lesson', 'previous_lesson', 'next_lesson', 'test_result',
            'purchased_course', 'test_exists', 'lessons', 'completed_lessons', 'item_type', 'inline_test_exists', 'inline_test', 'test_status', 'disable_next_btn','question_id'))->with('sequence',$sequence);
    }

    public function test($lesson_slug, Request $request)
    {
        $test = Test::where('slug', $lesson_slug)->firstOrFail();
        if(empty($request->get('questions'))) {
            return back()->with(['error'=>'Failed! Missing Options.']);
        }
        $answers = [];
        $test_score = 0;
        foreach ($request->get('questions') as $question_id => $answer_id) {
            $question = Question::find($question_id);
            $correct = QuestionsOption::where('question_id', $question_id)
                    ->where('id', $answer_id)
                    ->where('correct', 1)->count() > 0;
            $answers[] = [
                'question_id' => $question_id,
                'option_id' => $answer_id,
                'correct' => $correct
            ];
            if ($correct) {
                $test_score += $question->score;
            }
            /*
             * Save the answer
             * Check if it is correct and then add points
             * Save all test result and show the points
             */
        }
        $total_questions=count($request->get('questions'));
        $total_passed=$test_score;
        if($test->passing_percentage){
            $passing_percentage=$test->passing_percentage;
        } else {
            $passing_percentage=0;
        }
        if(($total_passed/$total_questions)*100 >= $passing_percentage) {
            $test_result = TestsResult::create([
                'test_id' => $test->id,
                'user_id' => \Auth::id(),
                'test_result' => $test_score,
            ]);
            $test_result->answers()->createMany($answers);
            if ($test->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
                $test->chapterStudents()->create([
                    'model_type' => $test->model_type,
                    'model_id' => $test->id,
                    'user_id' => auth()->user()->id,
                    'course_id' => $test->course->id
                ]);
            }
            return back()->with(['success' => 'Test passed successfully.','message'=>'Congratulations, you scored '.(($total_passed/$total_questions)*100).'%','result'=>$test_result]);
        }
        
        return back()->with(['error'=>'A score of '.$test->passing_percentage.'% or above is needed to pass the test.You scored '.(($total_passed/$total_questions)*100).'%']);
    }

    public function question(Request $request,$course_id,$question_id,$token='')
    {
        if(!$request->get('questions')) {
            return redirect()->back()->withErrors(['message' => 'Answer is required']);
        }
        $course = Course::where('id',$course_id)->firstOrFail();
        $question = Question::where('id',$question_id)->firstOrFail();
        $answers = [];
        $test_score = 0;
        $ans_id='';
        Session::put('qes_orders',$request->orders);
        Session::put('question_id',$question_id);
        Session::save();
        foreach ($request->get('questions') as $question_id => $answer_id) {
            $ans_id=$answer_id;
            $correct = QuestionsOption::where('question_id', $question_id)
                    ->where('id', $answer_id)
                    ->where('correct', 1)->count() > 0;
            $answers[] = [
                'question_id' => $question_id,
                'option_id' => $answer_id,
                'correct' => $correct
            ];
            if ($correct) {
                $test_score += $question->score;
            }
            /*
             * Save the answer
             * Check if it is correct and then add points
             * Save all test result and show the points
             */
        }
        $test_result = TestsResult::create([
            'question_id' => $question->id,
            'user_id' => \Auth::id(),
            'test_result' => $test_score,
        ]);
        $test_result->answers()->createMany($answers);

        if (ChapterStudent::where('model_id',$question->id)->where('course_id',$course->id)->where('user_id', \Auth::id())->get()->count() == 0) {
            ChapterStudent::create([
                'model_type' => "App\Models\Question",
                'model_id' => $question->id,
                'user_id' => auth()->user()->id,
                'course_id' => $course->id
            ]);
        }
        if($test_score)  {
        	return back()->with(['success' => 'Correct Answer'])->with('result',$test_result);
    	}
        // $test = TestsResult::where('id', '=', $test_result->id)
        //         ->where('user_id', '=', auth()->user()->id)
        //         ->first();
    	return redirect()->back()->withErrors(['message' => 'Incorrect Answer: Please review the chapter'])->with('res',0)->with('answer_id',$ans_id);
        return back()->with(['status' => 'error', 'message' => 'Please review the chapter','result'=>$test_result]);
    }

    public function testapi($lesson_slug, Request $request)
    {
        $test = Test::where('slug', $lesson_slug)->firstOrFail();
        $answers = [];
        $test_score = 0;
        foreach ($request->get('questions') as $question_id => $answer_id) {
            $question = Question::find($question_id);
            $correct_ans = QuestionsOption::where('question_id', $question_id)->where('correct', 1)->first();
            $correct = QuestionsOption::where('question_id', $question_id)
                    ->where('id', $answer_id)
                    ->where('correct', 1)->count() > 0;
                    $answers[] = [
                        'question_id' => $question_id,
                        'option_id' => $answer_id,
                        'correct' => $correct
                    ];
            if ($correct) {
                $test_score += $question->score;
            }
            /*
             * Save the answer
             * Check if it is correct and then add points
             * Save all test result and show the points
             */
        }
    
        $test_result = TestsResult::create([
            'test_id' => $test->id,
            'user_id' => \Auth::id(),
            'test_result' => $test_score,
        ]);

        $test_result->answers()->createMany($answers);


        if ($test->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
            $test->chapterStudents()->create([
                'model_type' => $test->model_type,
                'model_id' => $test->id,
                'user_id' => auth()->user()->id,
                'course_id' => $test->course->id
            ]);
        }
        return back()->with(['message'=>'Test score: ' . $test_score,'result'=>$test_result]);
    }

    public function retest(Request $request)
    {
        if($request->result_id){
            $test = TestsResult::where('id', '=', $request->result_id)
                ->where('user_id', '=', auth()->user()->id)
                ->first();
            $test->delete();
        }
        if($request->lesson_id){
            $lesson = Lesson::findorfail($request->lesson_id);
            return redirect()->route('lessons.show', ['course_id'=>$lesson->course_id, 'slug'=>$lesson->slug, 'type'=>'lesson']);
        }
        return back();
    }

    public function requestion(Request $request,$course_id,$question_id,$token='')
    {
        if($request->result_id){
            $test = TestsResult::where('id', '=', $request->result_id)
                ->where('user_id', '=', auth()->user()->id)
                ->first();
            $test->delete();
        }
        
        return redirect()->back();
    }

    public function videoProgress(Request $request)
    {
        $user = auth()->user();
        $video = Media::findOrFail($request->video);
        $video_progress = VideoProgress::where('user_id', '=', $user->id)
            ->where('media_id', '=', $video->id)->first() ?: new VideoProgress();
        $video_progress->media_id = $video->id;
        $video_progress->user_id = $user->id;
        $video_progress->duration = $video_progress->duration ?: round($request->duration, 2);
        $video_progress->progress = round($request->progress, 2);
        if ($video_progress->duration - $video_progress->progress < 5) {
            $video_progress->progress = $video_progress->duration;
            $video_progress->complete = 1;
        }
        $video_progress->save();
        return $video_progress->progress;
    }


    public function courseProgress(Request $request)
    {
        if (\Auth::check()) {
            $lesson = Lesson::find($request->model_id);
            if ($lesson != null) {
                if ($lesson->chapterStudents()->where('user_id', \Auth::id())->get()->count() == 0) {
                    $lesson->chapterStudents()->create([
                        'model_type' => $request->model_type,
                        'model_id' => $request->model_id,
                        'user_id' => auth()->user()->id,
                        'course_id' => $lesson->course->id
                    ]);
                    return true;
                }
            }
        }
        return false;
    }

    public function resetprogress(Request $request) {
        
        $course=Course::findOrFail($request->cid);
        $question=Question::find($request->id);
        $link='';
        if(!empty($question) && !empty($question->review_topic_id)) {
            $topic=Topic::find($question->review_topic_id);
            if($topic) {
                $timeline=CourseTimeline::where('course_id',$course->id)->where('model_id',$topic->id)->first();
                if($timeline) {
                    $link=route('lessons.show', [$course->id, (isset($topic->slug))?$topic->slug:$topic->id]).'/'.$topic->id.'/'.sha1(time()).'?seq='.$timeline->sequence;
                    $ids=CourseTimeline::where('course_id',$course->id)->where('sequence','>',$timeline->sequence)->pluck('model_id')->toArray();
                } else {
                    $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                    $continue_course  = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
                    if($continue_course == null){
                        $continue_course = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
                    }
                    $link=route('lessons.show', [$course->id, (isset($continue_course->model->slug))?$continue_course->model->slug:$topic->id]).'/'.$continue_course->model->id.'/'.sha1(time()).'?seq='.$continue_course->sequence;
                    $ids=CourseTimeline::where('course_id',$course->id)->pluck('model_id')->toArray();
                }
                $question_ids=ChapterStudent::where('model_type','App\Models\Question')->where('user_id',\Auth::id())->where('course_id',$course->id)->pluck('model_id')->toArray();
                ChapterStudent::where('user_id',\Auth::id())->where('course_id',$course->id)->whereIn('model_id',$ids)->delete();
                $test = TestsResult::whereIn('question_id',$question_ids)->delete();
                return redirect($link);
            }   
        }
        $timeline=CourseTimeline::where('course_id',$course->id)->where('sequence',1)->first();
        $link=route('lessons.show',['id' => $course->id,'slug'=>$timeline->model->slug]);
        if($timeline) {
            $link=route('lessons.show', [$course->id, (isset($timeline->model->slug))?$timeline->model->slug:$topic->id]).'/'.$timeline->model->id.'/'.sha1(time()).'?seq='.$timeline->sequence;
            $ids=CourseTimeline::where('course_id',$course->id)->where('sequence','>',$timeline->sequence)->pluck('model_id')->toArray();
        } else {
            $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
            $continue_course  = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
            if($continue_course == null){
                $continue_course = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
            }
            $link=route('lessons.show', [$course->id, (isset($continue_course->model->slug))?$continue_course->model->slug:$topic->id]).'/'.$continue_course->model->id.'/'.sha1(time()).'?seq='.$continue_course->sequence;
            $ids=CourseTimeline::where('course_id',$course->id)->pluck('model_id')->toArray();
        }
        $question_ids=ChapterStudent::where('model_type','App\Models\Question')->where('user_id',\Auth::id())->where('course_id',$course->id)->pluck('model_id')->toArray();
        $test = TestsResult::whereIn('question_id',$question_ids)->delete();
        ChapterStudent::where('user_id',\Auth::id())->where('course_id',$course->id)->whereIn('model_id',$ids)->delete();
        return redirect($link);
    }
    public function audiostatus(Request $request) {
        Session::put('audiostatus',$request->audiostatus);
        Session::save();
    }

}
