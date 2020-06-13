<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Bundle;
use App\Models\Contact;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use DB;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $purchased_courses = NULL;
        $available_courses = NULL;
        $students_count = NULL;
        $recent_reviews = NULL;
        $threads = NULL;
        $Supervisors_count = NULL;
        $courses_count = NULL;
        $pending_orders = NULL;
        $recent_orders = NULL;
        $recent_contacts = NULL;
        $purchased_bundles = NULL;
        if (\Auth::check()) {
            if(auth()->guard('vendor')->check()) {
                $user_ids = DB::table('users')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', '=', 'supervisor')
                    ->where('users.vendor_id', '=', auth()->user()->id)
                    ->pluck('users.id')
                    ->toArray();
                $courses_id = DB::table('course_user')->whereIn('user_id',$user_ids)
                    ->pluck('course_id')->toArray();
                $courses = DB::table('courses')->whereIn('id',$courses_id)->get();
                $acourses = DB::table('courses')->whereNotIn('id',$courses_id)->get();
                $purchased_courses = $courses;
                $available_courses = $acourses;
                $bundles_id = DB::table('bundle_courses')->whereIn('course_id',$courses_id)->pluck('bundle_id')->toArray();
                $bundles = Bundle::whereIn('id',$bundles_id)->get();
                $purchased_bundles = $bundles;
                $pending_orders = Order::where('status','=',0)->whereIn('user_id',$user_ids)->get();  
                $recent_reviews = Review::where('reviewable_type','=','App\Models\Course')
                    ->whereIn('reviewable_id',$courses_id)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
                $unreadThreads = [];
                $threads = [];
                if(auth()->user()->threads){
                    foreach(auth()->user()->threads as $item){
                        if($item->unreadMessagesCount > 0){
                            $unreadThreads[] = $item;
                        }else{
                            $threads[] = $item;
                        }
                    }
                    $threads = Collection::make(array_merge($unreadThreads,$threads))->take(10);
                }
                $students_count = count(auth()->user()->students());
                $Supervisors_count = count(auth()->user()->supervisors());
                $courses_count = count($courses);
                $recent_orders = Order::whereIn('user_id',auth()->user()->students())->orderBy('created_at','desc')->take(10)->get();
                $recent_contacts = array();
            } else {
                $purchased_courses = auth()->user()->purchasedCourses();
                if(auth()->user()->hasRole('student')) {
                    $available_courses = auth()->user()->availableCourses();
                }
                $purchased_bundles = auth()->user()->purchasedBundles();
                $pending_orders = auth()->user()->pendingOrders();
            }

            if(!auth()->guard('vendor')->check() && auth()->user()->hasRole('Supervisor')){
                //IF logged in user is Supervisor
                $students_count = Course::whereHas('Supervisors', function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                })
                    ->withCount('students')
                    ->get()
                    ->sum('students_count');


                $courses_id = auth()->user()->courses()->has('reviews')->pluck('id')->toArray();
                $recent_reviews = Review::where('reviewable_type','=','App\Models\Course')
                    ->whereIn('reviewable_id',$courses_id)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();



                $unreadThreads = [];
                $threads = [];
                if(auth()->user()->threads){
                    foreach(auth()->user()->threads as $item){
                        if($item->unreadMessagesCount > 0){
                            $unreadThreads[] = $item;
                        }else{
                            $threads[] = $item;
                        }
                    }
                    $threads = Collection::make(array_merge($unreadThreads,$threads))->take(10) ;

                }

            }elseif(!auth()->guard('vendor')->check() && auth()->user()->hasRole('administrator')){
                $students_count = User::role('student')->count();
                $Supervisors_count = User::role('Supervisor')->count();
                $courses_count = \App\Models\Course::all()->count();
                $recent_orders = Order::orderBy('created_at','desc')->take(10)->get();
                $recent_contacts = Contact::orderBy('created_at','desc')->take(10)->get();
            }
        }
        return view('backend.dashboard',compact('purchased_courses','available_courses','students_count','recent_reviews','threads','purchased_bundles','Supervisors_count','courses_count','recent_orders','recent_contacts','pending_orders'));
    }
}
