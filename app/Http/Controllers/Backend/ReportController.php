<?php

namespace App\Http\Controllers\Backend;

use App\Models\Bundle;
use App\Models\Course;
use App\Models\Auth\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\TestsResult;
use App\Models\CourseProgress;
use App\Models\ChapterStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\CourseTimeline;
use Carbon\Carbon;
class ReportController extends Controller
{
    public function getSalesReport()
    {
        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->supervisors();
            $courses = DB::table('courses')
                    ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->where('courses.published', '=', 1)
                    ->whereIn('course_user.user_id',$user_ids)
                    ->pluck('courses.id')
                    ->toArray();
            $bundles = DB::table('bundles')
                    ->join('bundle_courses', 'bundle_courses.course_id', '=', 'bundles.id')
                    ->where('bundles.published', '=', 1)
                    ->whereIn('bundle_courses.course_id',$courses)
                    ->pluck('bundles.id')
                    ->toArray();
        } else {
            $courses = Course::ofSupervisor()->where('published','=',1)->pluck('id');
            $bundles = Bundle::ofSupervisor()->where('published','=',1)->pluck('id');
        }

        $bundle_earnings = OrderItem::whereHas('order',function ($q){
            $q->where('status','=',1);
        })->where('item_type','=',Bundle::class)
            ->whereIn('item_id',$bundles);

        $bundle_sales = $bundle_earnings->count();
        $bundle_earnings = $bundle_earnings->sum('price');

        $course_earnings = OrderItem::whereHas('order',function ($q){
            $q->where('status','=',1);
        })->where('item_type','=',Course::class)
            ->whereIn('item_id',$courses);

        $course_sales = $course_earnings->count();
        $course_earnings = $course_earnings->sum('price');

        $total_earnings = $course_earnings+$bundle_earnings;
        $total_sales = $course_sales+$bundle_sales;


        return view('backend.reports.sales',compact('total_earnings','total_sales'));
    }

    public function getStudentsReport()
    {
        return view('backend.reports.students');
    }

    public function getVendorsReport()
    {
        return view('backend.reports.vendors');
    }
    public function getInvoicesReport()
    {
        return view('backend.reports.invoices');
    }

    public function getCourseData(Request $request)
    {
        $statues=array();
        if(isset($_GET['paid']) && $_GET['paid']) {
            array_push($statues, 1);
        }if(isset($_GET['unpaid']) && $_GET['unpaid']) {
            array_push($statues, 0);
        }
        if(empty($statues)) {
            array_push($statues, 1);
            array_push($statues, 0);
        }

        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->supervisors();
            $courses = DB::table('courses')
                    ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->where('courses.published', '=', 1)
                    ->whereIn('course_user.user_id',$user_ids)
                    ->pluck('courses.id')
                    ->toArray();
        } else {
            $courses = Course::ofSupervisor()->where('published','=',1)->pluck('id');
        }

        $course_orders = DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->join('courses', 'order_items.item_id', '=', 'courses.id')->join('clients', 'clients.id', '=', 'courses.client_id')->where('order_items.item_type','=',Course::class)
            ->whereIn('orders.status',$statues)
            ->whereIn('order_items.item_id',$courses)
            ->select('order_items.item_id','order_items.id', DB::raw('count(*) as orders, sum(order_items.price) as earnings, courses.title,courses.title as name, courses.slug,clients.name as client'))
            ->groupBy('item_id')
            ->get();
        return \DataTables::of($course_orders)
            ->addIndexColumn()
            ->editColumn('earnings',function($q){
                return "$".$q->earnings;
            })
            ->addColumn('course', function ($q) {
                $course_name = $q->title;
                $course_slug = $q->slug;
                $link = "<a href='".route('courses.show', [$course_slug])."' target='_blank'>".$course_name."</a>";
                return $link;
            })
            ->rawColumns(['course'])
            ->make();
    }

    public function getBundleData(Request $request)
    {
        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->supervisors();
            $courses = DB::table('courses')
                    ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->where('courses.published', '=', 1)
                    ->whereIn('course_user.user_id',$user_ids)
                    ->pluck('courses.id')
                    ->toArray();
            $bundles = DB::table('bundles')
                    ->join('bundle_courses', 'bundle_courses.course_id', '=', 'bundles.id')
                    ->join('bundle_student', 'bundle_student.bundle_id', '=', 'bundles.id')
                    ->where('bundles.published', '=', 1)
                    ->whereIn('bundle_courses.course_id',$courses)
                    ->pluck('bundles.id')
                    ->toArray();
        } else {
            $bundles = Bundle::ofSupervisor()->has('students','>',0)->withCount('students')->get();
        }

        $bundle_orders = OrderItem::whereHas('order',function ($q){
            $q->where('status','=',1);
        })->where('item_type','=',Bundle::class)
            ->whereIn('item_id',$bundles)
            ->join('bundles', 'order_items.item_id', '=', 'bundles.id')
            ->select('item_id', DB::raw('count(*) as orders, sum(order_items.price) as earnings, bundles.title as name, bundles.slug'))
            ->groupBy('item_id')
            ->get();


        return \DataTables::of($bundle_orders)
            ->addIndexColumn()
            ->addColumn('bundle', function ($q) {
                $bundle_name = $q->title;
                $bundle_slug = $q->slug;
                $link = "<a href='".route('bundles.show', [$bundle_slug])."' target='_blank'>".$bundle_name."</a>";
                return $link;
            })
            ->addColumn('students',function ($q){
                return $q->students_count;
            })
            ->rawColumns(['bundle'])
            ->make();
    }

    public function getStudentsData(Request $request){
        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->supervisors();
            $courses = DB::table('users')
                    ->join('course_student', 'course_student.user_id', '=', 'users.id')
                    ->join('courses', 'course_student.course_id', '=', 'courses.id')
                    ->join('orders', 'orders.user_id', '=', 'users.id')
                    // ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->join('vendors', 'vendors.id', '=', 'users.vendor_id')
                    ->where('courses.published', '=', 1)
                    ->whereIn('course_user.user_id',$user_ids)
                    ->select('users.first_name','users.id','users.id as user_id','users.last_name','users.email','users.confirmed','courses.title','vendors.company_name','orders.amount as amount_collected','orders.created_at','courses.price','orders.status','courses.id as course_id')
                    ->get();
        } elseif(auth()->user()->hasRole('supervisor')) {
            $courses = DB::table('users')
                    ->join('course_student', 'course_student.user_id', '=', 'users.id')
                    ->join('courses', 'course_student.course_id', '=', 'courses.id')
                    ->join('orders', 'orders.user_id', '=', 'users.id')
                    // ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->join('vendors', 'vendors.id', '=', 'users.vendor_id')
                    ->where('courses.published', '=', 1)
                    ->where('courses.client_id',auth()->user()->client_id)
                    ->select('users.first_name','users.id','users.id as user_id','users.last_name','users.email','users.confirmed','courses.title','vendors.company_name','orders.amount as amount_collected','orders.created_at','courses.price','orders.status','courses.id as course_id')
                    ->get();
        } else {
            $courses = DB::table('users')
                    ->join('course_student', 'course_student.user_id', '=', 'users.id')
                    ->join('courses', 'course_student.course_id', '=', 'courses.id')
                    ->join('orders', 'orders.user_id', '=', 'users.id')
                    // ->join('course_user', 'course_user.user_id', '=',  'course_student.user_id')
                    ->join('vendors', 'vendors.id', '=', 'users.vendor_id')
                    ->where('courses.published', '=', 1)
                    ->select('users.first_name','users.id','users.id as user_id','users.last_name','users.email','users.confirmed','courses.title','vendors.company_name','orders.amount as amount_collected','orders.created_at','courses.price','orders.status','courses.id as course_id')
                    ->get();
        }
        return \DataTables::of($courses)
            ->addIndexColumn()
            ->addColumn('completed', function ($q) {
                $count = 0;
                $student=User::find($q->user_id);
                if(!$student){
                    return 0;
                }
                if($student){
                    $completed_lessons =  $student->chapters()->where('course_id', $q->course_id)->get()->pluck('model_id')->toArray();
                    if (count($completed_lessons) > 0) {
                        $course=Course::find($q->course_id);
                        $progress = intval(count($completed_lessons) / $course->courseTimeline->count() * 100);
                        if($progress == 100){
                            $count++;
                        }
                    }
                }
                return $count;
            })->addColumn('last_viewed', function ($q) {
                $resume_link="#";
                $name="";
                $last_viewed=CourseProgress::where('course_id',$q->course_id)->where('user_id',$q->user_id)->orderBy('id','DESC')->first();
                if($last_viewed) {
                    $last_viewed_timeline=CourseTimeline::where('course_id', $q->course_id)->where('model_type',$last_viewed->model_type)->where('model_id',$last_viewed->model_id)->first();
                    $resume_link=route('lessons.show', [$last_viewed->course_id, (isset($last_viewed->model->slug))?$last_viewed->model->slug:$last_viewed->model_id]).'/'.$last_viewed->model_id.'/'.sha1(time()).'?seq='.(($last_viewed_timeline)?$last_viewed_timeline->sequence:'');
                    if(isset($last_viewed->model->title)) {
                        $name=$last_viewed->model->title;
                    } elseif($last_viewed->model_type=="App\Models\Question") {
                        $name="Question";
                    }elseif($last_viewed->model_type=="App\Models\Test") {
                        $name="Test";
                    }
                } else {
                    $completed_lessons = ChapterStudent::where('user_id',$q->user_id)->where('course_id', $q->course_id)->get()->pluck('model_id')->toArray();
                    $continue_course  = CourseTimeline::where('course_id', $q->course_id)->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
                    if($continue_course == null){
                        $continue_course = CourseTimeline::where('course_id', $q->course_id)->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
                    }
                    $resume_link=route('lessons.show', [$q->course_id, (isset($continue_course->model->slug))?$continue_course->model->slug:$continue_course->model_id]).'/'.$continue_course->model_id.'/'.sha1(time()).'?seq='.$continue_course->sequence;
                    if(isset($continue_course->model->title)) {
                        $name=$continue_course->model->title;
                    } elseif($continue_course->model_type=="App\Models\Question") {
                        $name="Question";
                    }elseif($continue_course->model_type=="App\Models\Test") {
                        $name="Test";
                    }
                }
                return array('name'=>$name,'resume_link'=>$resume_link);
            })->addColumn('score', function ($q) {
                $score = '';
                $student=User::find($q->user_id);
                if(!$student){
                    return '';
                }
                if($student){
                    $course=Course::find($q->course_id);
                    if($course) {
                        $timeline=CourseTimeline::where('course_id',$course->id)->where('model_type','App\Models\Test')->first();
                        if($timeline) {
                            $test_result = TestsResult::where('test_id', $timeline->model_id)->where('user_id', $q->user_id)->first();
                            if($test_result) {
                                $score=$test_result->test_result;
                            }
                        }
                    }
                }
                return $score;
            })->editColumn('created_at',function($q){
                // return date("d, F Y H:s:i", strtotime($q->created_at));
                return [
                   'display' => date("d, F Y H:s:i", strtotime($q->created_at)),
                   'timestamp' => Carbon::parse($q->created_at)->timestamp
                ];
            })->editColumn('expiry',function($q){
                // return date("d, F Y H:s:i", strtotime("+1 year",strtotime($q->created_at)));
                 return [
                   'display' => date("d, F Y H:s:i", strtotime("+1 year",strtotime($q->created_at))),
                   'timestamp' =>Carbon::parse($q->created_at)->addYear()->timestamp
                ];
            })
            ->make();
    }

    public function getVendorsData(Request $request){
        $from=(isset($_GET['from']))?$_GET['from']:'';
        $to=(isset($_GET['to']))?$_GET['to']:'';
        if(auth()->user()->hasRole('supervisor')) {
        $courses = DB::table('courses')
            ->join('course_user', 'course_user.course_id', '=', 'courses.id')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('vendors', 'vendors.id', '=', 'users.vendor_id')
            ->join('course_student', 'course_student.course_id', '=', 'courses.id')
            ->whereNotNull('users.vendor_id')
            ->where('model_has_roles.role_id',2)
            ->where('courses.client_id',auth()->user()->client_id)
            ->select('courses.title','courses.id','courses.id as course_id','vendors.contact_name as vendor','course_student.user_id as student_id')
            ->distinct()
            ->get();
        } else {
        $courses = DB::table('courses')
            ->join('course_user', 'course_user.course_id', '=', 'courses.id')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('vendors', 'vendors.id', '=', 'users.vendor_id')
            ->join('course_student', 'course_student.course_id', '=', 'courses.id')
            ->whereNotNull('users.vendor_id')
            ->where('model_has_roles.role_id',2)
            ->select('courses.title','courses.id','courses.id as course_id','vendors.contact_name as vendor','course_student.user_id as student_id')
            ->distinct()
            ->get();
        }
        return \DataTables::of($courses)
            ->addIndexColumn()
            ->addColumn('completed', function ($q) use($from,$to) {
                $count = 0;
                $course=Course::find($q->course_id);
                foreach ($course->students as $student){
                    $completed_lessons =  $student->chapters()->where('course_id', $q->course_id)->get()->pluck('model_id')->toArray();
                    if (count($completed_lessons) > 0) {
                        if(!empty($from) && !empty($to)) {
                            $progress = intval(count($completed_lessons) / $course->courseTimeline->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->count() * 100);
                        } else {
                            $progress = intval(count($completed_lessons) / $course->courseTimeline->count() * 100);
                        }
                        if($progress == 100){
                            $count++;
                        }
                    }
                }
                return $count;

            })
            ->make();
    }

    public function getinvoicesData(Request $request){

        $from=(isset($_GET['from']))?$_GET['from']:'';
        $to=(isset($_GET['to']))?$_GET['to']:'';
        $statues=array();
        if(isset($_GET['paid']) && $_GET['paid']) {
            array_push($statues, 1);
        }if(isset($_GET['unpaid']) && $_GET['unpaid']) {
            array_push($statues, 0);
        }
        if(empty($statues)) {
            array_push($statues, 1);
            array_push($statues, 0);
        }
        $user_ids=array();
        if(auth()->user()->hasRole('supervisor')) {
            $courses = DB::table('courses')->where('client_id',auth()->user()->client_id)->where('courses.published', '=', 1)->pluck('courses.id')->toArray();
        } else {
            $courses = DB::table('courses')->where('courses.published', '=', 1)->pluck('courses.id')->toArray();
        }
        if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
            $vendors=Vendor::where('created_by',auth()->user()->id)->pluck('id')->toArray();
            $user_ids = \DB::table('users')
                        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->where('roles.name', '=', 'student')
                        ->whereIn('users.vendor_id', $vendors)
                        ->pluck('users.id')
                        ->toArray();
            if(!empty($from) && !empty($to)) {
            $course_orders = OrderItem::where('item_type','=',Course::class)
                ->whereIn('item_id',$courses)
                ->join('courses', 'order_items.item_id', '=', 'courses.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_type',3)
                ->whereIn('orders.user_id',$user_ids)
                ->whereIn('orders.status',$statues)
                ->whereBetween('orders.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->select('orders.user_id','orders.reference_no','orders.payment_type','orders.created_at','courses.id as course_id','orders.status','courses.title','courses.price','orders.amount','orders.id','orders.invoice_number')
                ->orderBy('orders.id','desc')
                ->get();
            } else {
            $course_orders = OrderItem::where('item_type','=',Course::class)
                ->whereIn('item_id',$courses)
                ->join('courses', 'order_items.item_id', '=', 'courses.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_type',3)
                ->whereIn('orders.user_id',$user_ids)
                ->whereIn('orders.status',$statues)
                ->select('orders.user_id','orders.reference_no','orders.payment_type','orders.created_at','courses.id as course_id','orders.status','courses.title','courses.price','orders.amount','orders.id','orders.invoice_number')
                ->orderBy('orders.id','desc')
                ->get();
            }
        } else {
        if(!empty($from) && !empty($to)) {
            $course_orders = OrderItem::where('item_type','=',Course::class)
                ->whereIn('item_id',$courses)
                ->join('courses', 'order_items.item_id', '=', 'courses.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_type',3)
                ->whereIn('orders.status',$statues)
                ->whereBetween('orders.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->select('orders.user_id','orders.reference_no','orders.payment_type','orders.created_at','courses.id as course_id','orders.status','courses.title','courses.price','orders.amount','orders.id','orders.invoice_number')
                ->orderBy('orders.id','desc')
                ->get();
            } else {
            $course_orders = OrderItem::where('item_type','=',Course::class)
                ->whereIn('item_id',$courses)
                ->join('courses', 'order_items.item_id', '=', 'courses.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_type',3)
                ->whereIn('orders.status',$statues)
                ->select('orders.user_id','orders.reference_no','orders.payment_type','orders.created_at','courses.id as course_id','orders.status','courses.title','courses.price','orders.amount','orders.id','orders.invoice_number')
                ->orderBy('orders.id','desc')
                ->get();
            }
        }
        return \DataTables::of($course_orders)
            ->addIndexColumn()
            ->addColumn('progress', function ($q) use($from,$to) {
                $count = 0;
                $course=Course::find($q->course_id);
                $student=User::find($q->user_id);
                if($student && $course) {
                    $completed_lessons =  $student->chapters()->where('course_id', $q->course_id)->get()->pluck('model_id')->toArray();
                    if (!empty($completed_lessons) && count($completed_lessons) > 0) {
                        if(count($completed_lessons) >= CourseTimeline::where('course_id',$course->id)->count()) {
                            return "Pass";
                        } else {
                            return "InProgress";
                        }
                        
                    } else {
                        return 'InProgress';
                    }
                }
                return '';

            })->editColumn('user_id',function($q){
                $student=User::find($q->user_id);
                if($student)
                    return $student->first_name.' '.$student->last_name;
                else
                    return '';
            })->editColumn('price',function($q){
                return '$'.$q->price;
            })->editColumn('amount',function($q){
                return '$'.$q->amount;
            })->editColumn('invoice_number',function($q){
                $order=Order::find($q->id);
                if(!$q->invoice_number) {
                    if($order) {
                        $order->invoice_number=$order->id;
                        $order->save();
                        return $order->invoice_number;
                    }
                }
                return $order->invoice_number;

            })->addColumn('user_email',function($q){
                $student=User::find($q->user_id);
                if($student)
                    return $student->email;
                else
                    return '';
            })->addColumn('vendor_company_name',function($q){
                $student=User::find($q->user_id);
                if($student && !empty($student->vendor_id)){
                    $vendor=Vendor::find($student->vendor_id);
                    if($vendor)
                        return $vendor->company_name;
                }
                return '';
            })->addColumn('vendor_contact_name',function($q){
                $student=User::find($q->user_id);
                if($student && !empty($student->vendor_id)){
                    $vendor=Vendor::find($student->vendor_id);
                    if($vendor)
                        return $vendor->contact_name;
                }
                return '';
            })->addColumn('vendor_email',function($q){
                $student=User::find($q->user_id);
                if($student && !empty($student->vendor_id)){
                    $vendor=Vendor::find($student->vendor_id);
                    if($vendor)
                        return $vendor->contact_email;
                }
                return '';
            })->editColumn('status',function($q){
                if($q->status==1) {
                    return "Paid";
                }
                elseif($q->status==2) {
                    return "Failed";
                }
                else{
                    return "UnPaid";
                }
            })->editColumn('created_at',function($q){
                return [
                   'display' => date("d, F Y H:s:i", strtotime($q->created_at)),
                   'timestamp' => Carbon::parse($q->created_at)->timestamp
                ];
            })->addColumn('expiry',function($q){
                return date("d, F Y H:s:i", strtotime("+1 year",strtotime($q->created_at)));
            })
            ->make();
    }

    public function markAsPaid(Request $request) {
        if($request->ids) {
            Order::whereIn('id',explode(",", $request->ids))->update(['status'=>1]);
        }
        return redirect()->back()->with('success','Successfully marked as paid.');
    }

    public function markAsUnpaid(Request $request) {
        if($request->ids) {
            Order::whereIn('id',explode(",", $request->ids))->update(['status'=>0]);
        }
        return redirect()->back()->with('success','Successfully marked as unpaid.');
    }

    public function deleteInvoice(Request $request) {
        if($request->ids) {
            Order::whereIn('id',explode(",", $request->ids))->delete();
        }
        return redirect()->back()->with('success','Successfully Deleted.');
    }

    public function generateInvoiceGroup(Request $request) {
        $ids=explode(",", $request->ids);
        $orders=Order::whereIn('id',$ids)->get();
        $vendors=array();
        $order_ids=array();
        foreach ($orders as $key => $order) {
            array_push($order_ids, $order->id);
            $user=User::find($order->user_id);
            if($user) {
                array_push($vendors, $user->vendor_id);
            }
        }
        $vendors=Vendor::whereIn('id',$vendors)->get();
        $order_ids=implode(",", $order_ids);
        return view('backend.reports.pdfinvoices',compact('orders','vendors','order_ids'));
    }

    public function updateInvoiceID(Request $request) {
        $ids=explode(",", $request->order_ids);
        $orders=Order::whereIn('id',$ids)->get();
        foreach ($orders as $key => $order) {
            $order->invoice_number=$request->invoice_number;
            $order->save();
        }
        return json_encode('success');
    }
}
