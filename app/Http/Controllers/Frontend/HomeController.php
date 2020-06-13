<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Client;
use App\Models\Config;
use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Faq;
use App\Models\Vendor;
use App\Models\Lesson;
use App\Models\Topic;
use App\Models\Page;
use App\Models\Reason;
use App\Models\Sponsor;
use App\Models\System\Session;
use App\Models\Tag;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Newsletter;

/**
 * Class HomeController.
 */
class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */

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

    public function index()
    {
        if (request('page')) {
            $page = Page::where('slug', '=', request('page'))
                ->where('published', '=', 1)->first();
            if ($page != "") {
                return view($this->path . '.pages.index', compact('page'));
            }
            abort(404);
        }
        $type = config('theme_layout');
        $sections = Config::where('key', '=', 'layout_' . $type)->first();
        $sections = json_decode($sections->value);
        $client_ids=array();
        if(auth()->check() && auth()->user()->hasRole('student')) {
            // $supervisors=Vendor::findOrFail(auth()->user()->vendor_id)->supervisorsById(auth()->user()->vendor_id);
            try {
                $client_ids=Vendor::findOrFail(auth()->user()->vendor_id)->clientsById(auth()->user()->vendor_id);
            }catch(\Exception $e) {}
        }
        if(auth()->check() && auth()->user()->vendor_id) {
        $popular_courses = Course::withoutGlobalScope('filter')
            ->whereHas('Client')
            ->where('published', '=', 1)
            ->where('popular', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->take(6)->get();
        } else {
            $popular_courses=array();
        }
        $featured_courses = Course::withoutGlobalScope('filter')->where('published', '=', 1)
            ->whereHas('Client')
            ->where('featured', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->take(8)->get();

        $course_Clients = Client::with('courses')->where('icon', '!=', "")->take(12)->get();
        $trending_courses = Course::withoutGlobalScope('filter')
            ->whereHas('Client')
            ->where('published', '=', 1)
            ->where('trending', '=', 1)->where(function($query) use ($client_ids)  {
                if(!empty($client_ids)) {
                    $query->whereIn('client_id', $client_ids);
                }
             })->take(2)->get();

        $Supervisors = User::role('Supervisor')->with('courses')->where('active', '=', 1)->take(7)->get();

        $sponsors = Sponsor::where('status', '=', 1)->get();

        $news = Blog::orderBy('created_at', 'desc')->take(2)->get();

        $faqs = Client::with('faqs')->get()->take(6);

        $testimonials = Testimonial::where('status', '=', 1)->orderBy('created_at', 'desc')->get();

        $reasons = Reason::where('status', '=', 1)->orderBy('created_at', 'desc')->get();

        if ((int)config('counter') == 1) {
            $total_students = config('total_students');
            $total_courses = config('total_courses');
            $total_Supervisors = config('total_Supervisors');
        } else {
            $total_course = Course::where('published', '=', 1)->get()->count();
            $total_bundle = Bundle::where('published', '=', 1)->get()->count();
            $total_students = User::role('student')->get()->count();
            $total_courses = $total_course + $total_bundle;
            $total_Supervisors = User::role('Supervisor')->get()->count();
        }
        $vendors=array();
        if(config('registration_fields') != NULL) {
            $fields = json_decode(config('registration_fields'));
            $inputs = ['text','number','date'];
            foreach($fields as $item) {
                if($item->name=='vendor') {
                    $vendors = Vendor::get()->pluck('contact_name', 'id')->prepend('Please Select Vendor', '');
                }
            }
        }
        return view($this->path . '.index-' . config('theme_layout'), compact('vendors','popular_courses', 'featured_courses', 'sponsors', 'total_students', 'total_courses', 'total_Supervisors', 'testimonials', 'news', 'trending_courses', 'Supervisors', 'faqs', 'course_Clients', 'reasons', 'sections'));
    }

    public function getFaqs()
    {
        $faq_Clients = Client::has('faqs', '>', 0)->get();
        return view($this->path . '.faq', compact('faq_Clients'));
    }

    public function subscribe(Request $request)
    {
        $this->validate($request, [
            'email' => 'required'
        ]);

        if (config('mail_provider') != "" && config('mail_provider') == "mailchimp") {
            try {
                if (!Newsletter::isSubscribed($request->subs_email)) {
                    if (config('mailchimp_double_opt_in')) {
                        Newsletter::subscribePending($request->subs_email);
                        session()->flash('alert', "We've sent you an email, Check your mailbox for further procedure.");
                    } else {
                        Newsletter::subscribe($request->subs_email);
                        session()->flash('alert', "You've subscribed successfully");
                    }
                    return back();
                } else {
                    session()->flash('alert', "Email already exist in subscription list");
                    return back();

                }
            } catch (\Exception $e) {
                \Log::info($e->getMessage());
                session()->flash('alert', "Something went wrong, Please try again Later");
                return back();
            }

        } elseif (config('mail_provider') != "" && config('mail_provider') == "sendgrid") {
            try {
                $apiKey = config('sendgrid_api_key');
                $sg = new \SendGrid($apiKey);
                $query_params = json_decode('{"page": 1, "page_size": 1}');
                $response = $sg->client->contactdb()->recipients()->get(null, $query_params);
                if ($response->statusCode() == 200) {
                    $users = json_decode($response->body());
                    $emails = [];
                    foreach ($users->recipients as $user) {
                        array_push($emails, $user->email);
                    }
                    if (in_array($request->subs_email, $emails)) {
                        session()->flash('alert', "Email already exist in subscription list");
                        return back();
                    } else {
                        $request_body = json_decode(
                            '[{
                             "email": "' . $request->subs_email . '",
                             "first_name": "",
                             "last_name": ""
                              }]'
                        );
                        $response = $sg->client->contactdb()->recipients()->post($request_body);
                        if ($response->statusCode() != 201 || (json_decode($response->body())->new_count == 0)) {

                            session()->flash('alert', "Email already exist in subscription list");
                            return back();
                        } else {
                            $recipient_id = json_decode($response->body())->persisted_recipients[0];
                            $list_id = config('sendgrid_list');
                            $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->_($recipient_id)->post();
                            if ($response->statusCode() == 201) {
                                session()->flash('alert', "You've subscribed successfully");
                            } else {
                                session()->flash('alert', "Check your email and try again");
                                return back();
                            }

                        }
                    }
                }
            } catch (Exception $e) {
                \Log::info($e->getMessage());
                session()->flash('alert', "Something went wrong, Please try again Later");
                return back();
            }
        }

    }

    public function getSupervisors()
    {
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        $Supervisors = User::role('Supervisor')->paginate(12);
        return view($this->path . '.Supervisors.index', compact('Supervisors', 'recent_news'));
    }

    public function showSupervisor(Request $request)
    {
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        $Supervisor = User::role('Supervisor')->where('id', '=', $request->id)->first();
        $courses = NULL;
        if (count($Supervisor->courses) > 0) {
            $courses = $Supervisor->courses()->paginate(12);
        }
        return view($this->path . '.Supervisors.show', compact('Supervisor', 'recent_news', 'courses'));
    }

    public function getDownload(Request $request)
    {
        if (auth()->check()) {
            $lesson = Lesson::find($request->lesson);
            if(!$lesson) {
                $lesson=Topic::findOrfail($request->lesson);
            }
            $course_id = $lesson->course_id;
            $course = Course::findOrfail($course_id);
            $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
            if ($purchased_course || \Auth::user()->isAdmin()) {
                if(is_file(public_path() . "/storage/uploads/" . $request->filename)) {
                    $file = public_path() . "/storage/uploads/" . $request->filename;
                } elseif(is_file(public_path('/storage/uploads/'.$lesson->topic_audio))) {
                    $file = public_path('/storage/uploads/'.$lesson->topic_audio);
                }

                return Response::download($file);
            }
            return abort(404);

        }
        return abort(404);

    }

    public function searchCourse(Request $request)
    {

        if (request('type') == 'popular') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else if (request('type') == 'trending') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else if (request('type') == 'featured') {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else {
            $courses = Course::withoutGlobalScope('filter')->where('published', 1)->orderBy('id', 'desc')->paginate(12);
        }


        if ($request->Client != null) {
            $Client = Client::find((int)$request->Client);
            $ids = $Client->courses->pluck('id')->toArray();
            $types = ['popular', 'trending', 'featured'];
            if ($Client) {

                if (in_array(request('type'), $types)) {
                    $type = request('type');
                    $courses = $Client->courses()->where(function ($query) use ($request) {
                        $query->where('title', 'LIKE', '%' . $request->q . '%');
                        $query->orWhere('description', 'LIKE', '%' . $request->q . '%');
                    })
                        ->whereIn('id', $ids)
                        ->where('published', '=', 1)
                        ->where($type, '=', 1)
                        ->paginate(12);
                } else {
                    $courses = $Client->courses()
                        ->where(function ($query) use ($request) {
                            $query->where('title', 'LIKE', '%' . $request->q . '%');
                            $query->orWhere('description', 'LIKE', '%' . $request->q . '%');
                        })
                        ->where('published', '=', 1)
                        ->whereIn('id', $ids)
                        ->paginate(12);
                }

            }

        } else {
            $courses = Course::where('title', 'LIKE', '%' . $request->q . '%')
                ->orWhere('description', 'LIKE', '%' . $request->q . '%')
                ->where('published', '=', 1)
                ->paginate(12);

        }

        $Clients = Client::where('status', '=', 1)->get();


        $q = $request->q;
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();

        return view($this->path . '.search-result.courses', compact('courses', 'q', 'recent_news', 'Clients'));
    }


    public function searchBundle(Request $request)
    {

        if (request('type') == 'popular') {
            $bundles = Bundle::withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else if (request('type') == 'trending') {
            $bundles = Bundle::withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else if (request('type') == 'featured') {
            $bundles = Bundle::withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate(12);

        } else {
            $bundles = Bundle::withoutGlobalScope('filter')->where('published', 1)->orderBy('id', 'desc')->paginate(12);
        }


        if ($request->Client != null) {
            $Client = Client::find((int)$request->Client);
            $ids = $Client->bundles->pluck('id')->toArray();
            $types = ['popular', 'trending', 'featured'];
            if ($Client) {

                if (in_array(request('type'), $types)) {
                    $type = request('type');
                    $bundles = $Client->bundles()->where(function ($query) use ($request) {
                        $query->where('title', 'LIKE', '%' . $request->q . '%');
                        $query->orWhere('description', 'LIKE', '%' . $request->q . '%');
                    })
                        ->whereIn('id', $ids)
                        ->where('published', '=', 1)
                        ->where($type, '=', 1)
                        ->paginate(12);
                } else {
                    $bundles = $Client->bundles()
                        ->where(function ($query) use ($request) {
                            $query->where('title', 'LIKE', '%' . $request->q . '%');
                            $query->orWhere('description', 'LIKE', '%' . $request->q . '%');
                        })
                        ->where('published', '=', 1)
                        ->whereIn('id', $ids)
                        ->paginate(12);
                }

            }

        } else {
            $bundles = Bundle::where('title', 'LIKE', '%' . $request->q . '%')
                ->orWhere('description', 'LIKE', '%' . $request->q . '%')
                ->where('published', '=', 1)
                ->paginate(12);

        }

        $Clients = Client::where('status', '=', 1)->get();


        $q = $request->q;
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();

        return view($this->path . '.search-result.bundles', compact('bundles', 'q', 'recent_news', 'Clients'));
    }

    public function searchBlog(Request $request)
    {
        $blogs = Blog::where('title', 'LIKE', '%' . $request->q . '%')
            ->paginate(12);
        $Clients = Client::has('blogs')->where('status', '=', 1)->paginate(10);
        $popular_tags = Tag::has('blogs', '>', 4)->get();


        $q = $request->q;
        return view($this->path . '.search-result.blogs', compact('blogs', 'q', 'Clients', 'popular_tags'));
    }

    public function getvendors(Request $request) {
        $query=$request->q;
        $vendors=Vendor::where('company_name', 'LIKE', '%'.$query.'%')->get();
        $data=array();
        foreach ($vendors as $key => $value) {
            array_push($data, array('company_name'=>$value->company_name,'id'=>$value->id));
        }
        if(empty($data)) {
            array_push($data,array('company_name'=>'Vendor Company not listed','id'=>'Vendor_Company_not_listed'));
            array_push($data,array('company_name'=>'Individual Contractor','id'=>'Individual_Contractor'));
            return json_encode($data);
        }
        return json_encode($data);
    }
}

