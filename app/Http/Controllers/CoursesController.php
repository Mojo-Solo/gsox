<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Client;
use App\Models\Course;
use App\Models\Review;
use App\Models\CourseTimeline;
use App\Models\Vendor;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Cart;

class CoursesController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if(session()->has('display_type')){
            if(session('display_type') == 'rtl'){
                $path = 'frontend-rtl';
            }else{
                $path = 'frontend';
            }
        }else if(config('app.display_type') == 'rtl'){
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    public function all($slug='')
    {
        $client_ids=array();
        $client='';
        $client_object='';
        if(!empty($slug)) {
            $client_result=Client::where('slug',$slug)->first();
            $client_object=$client_result;
            if($client_result) {
                array_push($client_ids, $client_result->id);
                $client=$client_result->name;
            } else {
                abort(404);
            }
        }
        if(auth()->check() && auth()->user()->hasRole('student')) {
            // $supervisors=Vendor::findOrFail(auth()->user()->vendor_id)->supervisorsById(auth()->user()->vendor_id);
            $client_ids=Vendor::findOrFail(auth()->user()->vendor_id)->clientsById(auth()->user()->vendor_id);
            $client=Client::findOrFail($client_ids[0])->name;
            $client_object=Client::findOrFail($client_ids[0]);
        }
        if(auth()->check() && auth()->user()->hasRole('supervisor')) {
            // $supervisors=Vendor::findOrFail(auth()->user()->vendor_id)->supervisorsById(auth()->user()->vendor_id);
            $client_ids=array(\Auth::user()->client_id);
            $client=Client::findOrFail(\Auth::user()->client_id)->name;
            $client_object=Client::findOrFail(\Auth::user()->client_id);
        }
        if(auth()->guard('vendor')->check()) {
            $client_ids=array();
            if(!empty(auth()->guard('vendor')->user()->clients)) {
                $client_ids=explode(",", auth()->guard('vendor')->user()->clients);
                $client=Client::findOrFail($client_ids[0])->name;
                $client_object=Client::findOrFail($client_ids[0]);
            } else {
                $client='';
            }
        }
        if (request('type') == 'popular') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->orderBy('title', 'asc')->paginate(12);

        } else if (request('type') == 'trending') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->orderBy('title', 'asc')->paginate(12);

        } else if (request('type') == 'featured') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->orderBy('title', 'asc')->paginate(12);

        } else {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->orderBy('title', 'asc')->paginate(12);
        }
        $purchased_courses = NULL;
        $purchased_bundles = NULL;
        $Clients = Client::where('status','=',1)->get();

        if (\Auth::check()) {
            $purchased_courses = Course::where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);    
                }
             })->withoutGlobalScope('filter')->whereHas('students', function ($query) {
                $query->where('id', \Auth::id());
            })
                ->with('lessons')
                ->orderBy('id', 'desc')
                ->get();
        }
        $featured_courses = Course::withoutGlobalScope('filter')->where('published', '=', 1)
            ->where('featured', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->take(8)->get();
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        return view( $this->path.'.courses.index', compact('courses', 'purchased_courses', 'recent_news','featured_courses','Clients','client_object'))->with('client',$client);
    }

    public function show($course_slug)
    {
        $continue_course=NULL;
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->with('publishedLessons')->first();
        $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        if(($course->published == 0) && ($purchased_course == false)){
            abort(404);
        }
        $course_rating = 0;
        $total_ratings = 0;
        $completed_lessons = "";
        $is_reviewed = false;
        if(auth()->check() && $course->reviews()->where('user_id','=',auth()->user()->id)->first()){
            $is_reviewed = true;
        }
        if ($course->reviews->count() > 0) {
            $course_rating = $course->reviews->avg('rating');
            $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
        }
        // $lessons = $course->courseTimeline()->orderby('sequence','asc')->get();
        $lessons = CourseTimeline::where('course_id',$course->id)->where('model_type','App\Models\Lesson')->orderby('sequence','asc')->get();

        if (\Auth::check()) {

            $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
            $continue_course  = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
            if($continue_course == null){
                $continue_course = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
            }

        }
        
        return view( $this->path.'.courses.course', compact('course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons','total_ratings','is_reviewed','lessons','continue_course'));
    }


    public function rating($course_id, Request $request)
    {
        $course = Course::findOrFail($course_id);
        $course->students()->updateExistingPivot(\Auth::id(), ['rating' => $request->get('rating')]);

        return redirect()->back()->with('success', 'Thank you for rating.');
    }

    public function getByClient(Request $request)
    {
        $Client = Client::where('slug', '=', $request->Client)
            ->where('status','=',1)
            ->first();
        $Clients = Client::where('status','=',1)->get();
        $client_ids=array();
        if(auth()->check()) {
            // $supervisors=Vendor::findOrFail(auth()->user()->vendor_id)->supervisorsById(auth()->user()->vendor_id);
            $client_ids=Vendor::findOrFail(auth()->user()->vendor_id)->clientsById(auth()->user()->vendor_id);
        }
        if ($Client != "") {
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $featured_courses = Course::where('published', '=', 1)
                ->where('featured', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->take(8)->get();

            if (request('type') == 'popular') {
                $courses = $Client->courses()->withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->orderBy('title', 'asc')->paginate(12);

            } else if (request('type') == 'trending') {
                $courses = $Client->courses()->withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->orderBy('title', 'asc')->paginate(12);

            } else if (request('type') == 'featured') {
                $courses = $Client->courses()->withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->orderBy('title', 'asc')->paginate(12);

            } else {
                $courses = $Client->courses()->withoutGlobalScope('filter')->where('published', 1)->orderBy('title', 'asc')->paginate(12);
            }


            return view( $this->path.'.courses.index', compact('courses', 'Client', 'recent_news','featured_courses','Clients'));
        }
        return abort(404);
    }

    public function addReview(Request $request)
    {
        $this->validate($request, [
            'review' => 'required'
        ]);
        $course = Course::findORFail($request->id);
        $review = new Review();
        $review->user_id = auth()->user()->id;
        $review->reviewable_id = $course->id;
        $review->reviewable_type = Course::class;
        $review->rating = $request->rating;
        $review->content = $request->review;
        $review->save();

        return back();
    }

    public function editReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $course = $review->reviewable;
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
            $course_rating = 0;
            $total_ratings = 0;
            $lessons = $course->courseTimeline()->orderby('sequence','asc')->get();

            if ($course->reviews->count() > 0) {
                $course_rating = $course->reviews->avg('rating');
                $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
            }
            if (\Auth::check()) {

                $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                $continue_course  = $course->courseTimeline()->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
                if($continue_course == ""){
                    $continue_course = $course->courseTimeline()->orderby('sequence','asc')->first();
                }

            }
            return view( $this->path.'.courses.course', compact('course', 'purchased_course', 'recent_news','completed_lessons','continue_course', 'course_rating', 'total_ratings','lessons', 'review'));
        }
        return abort(404);

    }


    public function updateReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $review->rating = $request->rating;
            $review->content = $request->review;
            $review->save();

            return redirect()->route('courses.show', ['slug' => $review->reviewable->slug]);
        }
        return abort(404);

    }

    public function deleteReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $slug = $review->reviewable->slug;
            $review->delete();
            return redirect()->route('courses.show', ['slug' => $slug]);
        }
        return abort(404);
    }
}
