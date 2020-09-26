@extends('backend.layouts.app')

@section('title', __('strings.backend.dashboard.title').' | '.app_name())
@if(auth()->user()->hasRole('student'))
@section('frontstyle')
<link rel="stylesheet" type="text/css" href="{{url('public/css/frontend.css')}}">
@stop
@endif
@push('after-styles')
    <style>
        .trend-badge-2 {
            top: -10px;
            left: -52px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            position: absolute;
            padding: 40px 40px 12px;
            -webkit-transform: rotate(-45deg);
            transform: rotate(-45deg);
            background-color: #ff5a00;
        }

        .progress {
            background-color: #b6b9bb;
            height: 2em;
            font-weight: bold;
            font-size: 0.8rem;
            text-align: center;
        }
        a{
            text-decoration: none !important;
        }
        .best-course-pic {
            background-color: #333333 !important;
            background-position: center !important;
            background-size: cover !important;
            height: 150px !important;
            width: 100% !important;
            background-repeat: no-repeat !important;
            border-radius: 0px !important;
        }
        a {
            color: #20a8d8;
            text-decoration: none;
            background-color: transparent;
        }
        .best-course-pic-text {
            border-radius: 0px;
            padding-top: 0px;
            margin-bottom: 10px;
        }
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger, .bg-dark {
            color: #fff !important;
        }
        .backend a  {
            padding: 0px; 
        }
        .backend .course-meta span {
            margin-right: 0px !important;
        }.frontend .course-meta span {
            margin-right: 3px !important;
        }
        .frontend .best-course-pic-text .best-course-text {
            background-color: #eee;
            padding: 10px;
        }
        .giveMeEllipsis {
           overflow: hidden;
           text-overflow: ellipsis;
           display: -webkit-box;
           -webkit-box-orient: vertical;
           -webkit-line-clamp: 2;
        }
        .best-course-pic-text .course-price {
            bottom: 5px;
            left: 5px;
        }.frontend .best-course-pic-text {
            min-height: 275px;
            max-height: 275px;
            background-color: #eee;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <strong>@lang('strings.backend.dashboard.welcome') {{ $logged_in_user->name }}!</strong>
                </div><!--card-header-->
                <div class="card-body">
                    <div class="row">
                        @if(auth()->user()->hasRole('student'))


                            @if(count($pending_orders) > 0)
                                <div class="col-12">
                                    <h4>@lang('labels.backend.dashboard.pending_orders')</h4>
                                </div>
                                <div class="col-12 text-center">

                                    <table class="table table table-bordered table-striped">
                                        <thead>
                                        <tr>

                                            <th>@lang('labels.general.sr_no')</th>
                                            <th>@lang('labels.backend.orders.fields.reference_no')</th>
                                            <th>@lang('labels.backend.orders.fields.items')</th>
                                            <th>@lang('labels.backend.orders.fields.amount')
                                                <small>(in {{$appCurrency['symbol']}})</small>
                                            </th>
                                            <th>@lang('labels.backend.orders.fields.payment_status.title')</th>
                                            <th>@lang('labels.backend.orders.fields.date')</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pending_orders as $key=>$item)
                                            @php $key++ @endphp
                                            <tr>
                                                <td>
                                                    {{$key}}
                                                </td>
                                                <td>
                                                    {{$item->reference_no}}
                                                </td>
                                                <td>
                                                    @foreach($item->items as $key=>$subitem)
                                                        @php $key++ @endphp
                                                        @if($subitem->item != null)
                                                            {{$key.'. '.$subitem->item->title}} <br>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <?php 
                                                    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                                                    echo $f->format($item->amount);
                                                    ?>
                                                </td>
                                                <td>
                                                    @if($item->status == 0)
                                                        @lang('labels.backend.dashboard.pending')
                                                    @elseif($item->status == 1)
                                                        @lang('labels.backend.dashboard.success')
                                                    @elseif($item->status == 2)
                                                        @lang('labels.backend.dashboard.failed')
                                                    @endif
                                                </td>
                                                <td>
                                                    {{$item->created_at->format('d-m-Y h:i:s')}}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            @endif

                            <div class="col-12">
                                <h4>@lang('labels.backend.dashboard.my_courses')</h4>
                            </div>


                            @if(count($purchased_courses) > 0)
                                @foreach($purchased_courses as $item)
                                   
                                    <div class="col-md-3 backend">
                                        <div class="best-course-pic-text position-relative border">
                                            <div class="best-course-pic position-relative overflow-hidden"
                                                 @if($item->course_image != "") style="background-image: url({{asset('storage/uploads/'.$item->course_image)}})" @endif>

                                                @if($item->trending == 1)
                                                    <div class="trend-badge-2 text-center text-uppercase">
                                                        <i class="fas fa-bolt"></i>
                                                        <span>@lang('labels.backend.dashboard.trending') </span>
                                                    </div>
                                                @endif

                                                <div class="course-rate ul-li">
                                                    <ul>
                                                        @for($i=1; $i<=(int)$item->rating; $i++)
                                                            <li><i class="fas fa-star"></i></li>
                                                        @endfor
                                                    </ul>
                                                </div>
                                                @if($item->isUserEnrolled())
                                                <div class="course-price text-center {{($item->progress()==100)?'btn-success':'gradient-bg'}}">
                                                <?php
                                                    $completed_lessons = \Auth::user()->chapters()->where('course_id', $item->id)->get()->pluck('model_id')->toArray();
                                                    $continue_course  = $item->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
                                                    if($continue_course == null){
                                                        $continue_course = $item->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
                                                    }
                                                    $resume_link="#";
                                                    $last_viewed=App\Models\CourseProgress::where('course_id',$item->id)->where('user_id',\Auth::user()->id)->orderBy('id','DESC')->first();
                                                    if($last_viewed) {
                                                        $last_viewed_timeline=$item->courseTimeline()->where('model_type',$last_viewed->model_type)->where('model_id',$last_viewed->model_id)->first();
                                                        $resume_link=route('lessons.show', [$last_viewed->course_id, (isset($last_viewed->model->slug))?$last_viewed->model->slug:$last_viewed->model_id]).'/'.$last_viewed->model_id.'/'.sha1(time()).'?seq='.(($last_viewed_timeline)?$last_viewed_timeline->sequence:'');
                                                    } else {
                                                        $resume_link=route('lessons.show', [$item->id, (isset($continue_course->model->slug))?$continue_course->model->slug:$continue_course->model_id]).'/'.$continue_course->model_id.'/'.sha1(time()).'?seq='.$continue_course->sequence;
                                                    }
                                                ?>
                                                <a href="{{ $resume_link }}" class="text-white text-center text-uppercase  bold-font btn-sm">
                                                @if(auth()->check() && auth()->user()->hasRole('student') && auth()->user()->chapters()->where('course_id', $item->id)->count())
                                                    @if($item->progress()==100)
                                                    Completed
                                                    @else
                                                    Resume
                                                    @endif
                                                @else
                                                Start Course
                                                @endif
                                                </a>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="best-course-text d-inline-block w-100 p-2">
                                                <div class="course-title mb20 headline relative-position">
                                                    <h5>
                                                        <a href="{{ route('courses.show', [$item->slug]) }}" class="giveMeEllipsis">{{$item->title}}</a>
                                                    </h5>
                                                </div>
                                                <div class="course-meta d-inline-block w-100 ">
                                                    <div class="d-inline-block w-100 0 mt-2">
                                                     <span class="course-Client float-left">
                                                <a href="{{url('courses/'.$item->Client->slug)}}"
                                                   class="bg-success text-decoration-none px-2 p-1">{{$item->Client->name}}</a>
                                            </span>
                                                        <span class="course-author float-right">
                                                 {{ $item->students()->count() }}
                                                            @lang('labels.backend.dashboard.students')
                                            </span>
                                                    </div>

                                                    <div class="progress my-2">
                                                        <div class="progress-bar"
                                                             style="width:{{$item->progress() }}%"><span class="text-white" style="padding:5px;">{{ $item->progress()}} % @lang('labels.backend.dashboard.completed')
                                                        </div>
                                                    </div>
                                                    @if($item->progress() == 100)
                                                        @if(!$item->isUserCertified())
                                                            <form method="post"
                                                                  action="{{route('admin.certificates.generate')}}">
                                                                @csrf
                                                                <input type="hidden" value="{{$item->id}}"
                                                                       name="course_id">
                                                                <button class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                                        id="finish">@lang('labels.frontend.course.finish_course')</button>
                                                            </form>
                                                        @else
                                                            <div class="alert alert-success px-1 text-center mb-0">
                                                                @lang('labels.frontend.course.certified')
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(count($available_courses) > 0)
                            <hr>
                            <div class="col-12">
                                <h4 class="mt-4 mb-4 pt-2" style="border-top: 1px solid;">Available Courses</h4>
                            </div>
                                @foreach($available_courses as $course)
                                    <div class="col-md-3 frontend">
                                        <div class="best-course-pic-text relative-position border">
                                            <div class="best-course-pic relative-position"
                                                 @if($course->course_image != "") style="background-image: url('{{asset('storage/uploads/'.$course->course_image)}}')" @endif>

                                                @if($course->trending == 1)
                                                    <div class="trend-badge-2 text-center text-uppercase">
                                                        <i class="fas fa-bolt"></i>
                                                        <span>@lang('labels.frontend.badges.trending')</span>
                                                    </div>
                                                @endif
                                                    @if($course->free == 1)
                                                        <div class="trend-badge-3 text-center text-uppercase">
                                                            <i class="fas fa-bolt"></i>
                                                            <span>@lang('labels.backend.courses.fields.free')</span>
                                                        </div>
                                                    @endif
                                                @if(!\Session::has('user_sposor') && empty(\Session::get('user_sposor')))
                                                <div class="course-price text-center gradient-bg">
                                                    @if($course->free == 1 && !$course->isUserEnrolled())
                                                        <span>{{trans('labels.backend.courses.fields.free')}}</span>
                                                    @else
                                                        <span> {{$appCurrency['symbol'].' '.$course->price}}</span>
                                                    @endif
                                                </div>
                                                @endif
                                                <div class="course-rate ul-li">
                                                    <ul>
                                                        @for($i=1; $i<=(int)$course->rating; $i++)
                                                            <li><i class="fas fa-star"></i></li>
                                                        @endfor
                                                    </ul>
                                                </div>
                                                <div class="course-details-btn">
                                                    <a href="{{ route('courses.show', [$course->slug]) }}">@lang('labels.frontend.course.course_detail')
                                                        <i class="fas fa-arrow-right"></i></a>
                                                </div>
                                                <div class="blakish-overlay"></div>
                                            </div>
                                            <div class="best-course-text">
                                                <div class="course-title mb20 headline relative-position">
                                                    <h3>
                                                        <a href="{{ route('courses.show', [$course->slug]) }}" class="giveMeEllipsis">{{$course->title}}</a>
                                                    </h3>
                                                </div>
                                                <div class="course-meta">
                                                    <span class="course-Client"><a
                                                                href="{{url('courses/'.$course->Client->slug)}}"><span class="badge badge-info text-white">{{$course->Client->name}}</span></a></span>
                                                    <span class="course-author"><a class="ml-4" href="#">{{ $course->students()->count() }}
                                                            @lang('labels.frontend.course.students')</a></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if(count($available_courses) == 0 && count($purchased_courses) == 0)
                                <div class="col-12 text-center">
                                    <h4 class="text-center">@lang('labels.backend.dashboard.no_data')</h4>
                                    <a class="btn btn-primary"
                                       href="{{route('courses.all')}}">@lang('labels.backend.dashboard.buy_course_now')
                                        <i class="fa fa-arrow-right"></i></a>
                                </div>
                            @endif
                            @if(count($purchased_bundles) > 0)

                                <div class="col-12 mt-5">
                                    <h4>@lang('labels.backend.dashboard.my_course_bundles')</h4>
                                </div>
                                @foreach($purchased_bundles as $key=>$bundle)
                                    @php $key++ @endphp
                                    <div class="col-12"><h5><a
                                                    href="{{route('bundles.show',['slug'=>$bundle->slug ])}}">
                                                {{$key.'. '.$bundle->title}}</a></h5>
                                    </div>
                                    @if(count($bundle->courses) > 0)
                                        @foreach($bundle->courses as $item)
                                            <div class="col-md-3 mb-5">
                                                <div class="best-course-pic-text position-relative border">
                                                    <div class="best-course-pic position-relative overflow-hidden"
                                                         @if($item->course_image != "") style="background-image: url({{asset('storage/uploads/'.$item->course_image)}})" @endif>

                                                        @if($item->trending == 1)
                                                            <div class="trend-badge-2 text-center text-uppercase">
                                                                <i class="fas fa-bolt"></i>
                                                                <span>@lang('labels.backend.dashboard.trending') </span>
                                                            </div>
                                                        @endif

                                                        <div class="course-rate ul-li">
                                                            <ul>
                                                                @for($i=1; $i<=(int)$item->rating; $i++)
                                                                    <li><i class="fas fa-star"></i></li>
                                                                @endfor
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="best-course-text d-inline-block w-100 p-2">
                                                        <div class="course-title mb20 headline relative-position">
                                                            <h5>
                                                                <a href="{{ route('courses.show', [$item->slug]) }}">{{$item->title}}</a>
                                                            </h5>
                                                        </div>
                                                        <div class="course-meta d-inline-block w-100 ">
                                                            <div class="d-inline-block w-100 0 mt-2">
                                                     <span class="course-Client float-left">
                                                <a href="{{route('courses.Client',['Client'=>$item->Client->slug])}}"
                                                   class="bg-success text-decoration-none px-2 p-1">{{$item->Client->name}}</a>
                                            </span>
                                                                <span class="course-author float-right">
                                                 {{ $item->students()->count() }}
                                                                    @lang('labels.backend.dashboard.students')
                                            </span>
                                                            </div>

                                                            <div class="progress my-2">
                                                                <div class="progress-bar"
                                                                     style="width:{{$item->progress() }}%">{{ $item->progress()  }}
                                                                    %
                                                                    @lang('labels.backend.dashboard.completed')
                                                                </div>
                                                            </div>
                                                            @if($item->progress() == 100)
                                                                @if(!$item->isUserCertified())
                                                                    <form method="post"
                                                                          action="{{route('admin.certificates.generate')}}">
                                                                        @csrf
                                                                        <input type="hidden" value="{{$item->id}}"
                                                                               name="course_id">
                                                                        <button class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                                                id="finish">@lang('labels.frontend.course.finish_course')</button>
                                                                    </form>
                                                                @else
                                                                    <div class="alert alert-success px-1 text-center mb-0">
                                                                        @lang('labels.frontend.course.certified')
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                    </div>
                    @endif
                    @elseif(auth()->user()->hasRole('supervisor'))
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 col-12 border-right">
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{url('user/courses')}}">
                                            <div class="card text-white bg-primary text-center">
                                                <div class="card-body">
                                                    <h2 class="">{{(auth()->guard('vendor')->check())?(count($purchased_bundles)+count($purchased_courses)):count(auth()->user()->courses)}}</h2>
                                                    <h5>@lang('labels.backend.courses.title')</h5>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                        <div class="col-12">
                                            <a href="{{url('user/students')}}">
                                            <div class="card text-white bg-success text-center">
                                                <div class="card-body">
                                                    <h2 class="">{{$students_count}}</h2>
                                                    <h5>@lang('labels.backend.dashboard.students_enrolled')</h5>
                                                </div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @if(false)
                                <div class="{{(auth()->guard('vendor')->check())?'col-md-9':'col-md-5'}} col-12 border-right">
                                    <div class="d-inline-block form-group w-100">
                                        <h4 class="mb-0">@lang('labels.backend.dashboard.recent_reviews') <a
                                                    class="btn btn-primary float-right"
                                                    href="{{route('admin.reviews.index')}}">@lang('labels.backend.dashboard.view_all')</a>
                                        </h4>

                                    </div>
                                    <table class="table table-responsive-sm table-striped">
                                        <thead>
                                        <tr>
                                            <td>@lang('labels.backend.dashboard.course')</td>
                                            <td>@lang('labels.backend.dashboard.review')</td>
                                            <td>@lang('labels.backend.dashboard.time')</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($recent_reviews) > 0)
                                            @foreach($recent_reviews as $item)
                                                <tr>
                                                    <td>
                                                        <a target="_blank"
                                                           href="{{route('courses.show',[$item->reviewable->slug])}}">{{$item->reviewable->title}}</a>
                                                    </td>
                                                    <td>{{$item->content}}</td>
                                                    <td>{{$item->created_at->diffforhumans()}}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3">@lang('labels.backend.dashboard.no_data')</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                                @if(!auth()->guard('vendor')->check())
                                <div class="col-md-9 col-12">
                                    <div class="d-inline-block form-group w-100">
                                        <h4 class="mb-0">@lang('labels.backend.dashboard.recent_messages') <a
                                                    class="btn btn-primary float-right"
                                                    href="{{route('admin.messages')}}">@lang('labels.backend.dashboard.view_all')</a>
                                        </h4>
                                    </div>


                                    <table class="table table-responsive-sm table-striped">
                                        <thead>
                                        <tr>
                                            <td>@lang('labels.backend.dashboard.message_by')</td>
                                            <td>@lang('labels.backend.dashboard.message')</td>
                                            <td>@lang('labels.backend.dashboard.time')</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($threads) > 0)
                                            @foreach($threads as $item)
                                                <tr>
                                                    <td>
                                                        <a target="_blank"
                                                           href="{{asset('/user/messages/?thread='.$item->id)}}">{{$item->title}}</a>
                                                    </td>
                                                    <td>{{$item->lastMessage->body}}</td>
                                                    <td>{{$item->lastMessage->created_at->diffForHumans() }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3">@lang('labels.backend.dashboard.no_data')</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>

                    @elseif(auth()->user()->hasRole('administrator') || auth()->guard('vendor')->check())
                        <div class="col-md-4 col-12">
                            <a href="{{url('user/courses')}}">
                            <div class="card text-white bg-dark text-center py-3">
                                <div class="card-body">
                                    <h1 class="">{{$courses_count}}</h1>
                                    <h3>@lang('labels.backend.courses.title')</h3>
                                </div>
                            </div>
                            </a>
                        </div>

                        <div class="col-md-4 col-12">
                            <a href="{{url('user/students')}}">
                            <div class="card text-white bg-light text-dark text-center py-3">
                                <div class="card-body">
                                    <h1 class="">{{$students_count}}</h1>
                                    <h3>@lang('labels.backend.dashboard.students')</h3>
                                </div>
                            </div>
                            </a>
                        </div>
                        <div class="col-md-4 col-12">
                            <a href="{{url('user/Supervisors')}}">
                            <div class="card text-white bg-primary text-center py-3">
                                <div class="card-body">
                                    <h1 class="">{{$Supervisors_count}}</h1>
                                    <h3>@lang('labels.backend.dashboard.Supervisors')</h3>
                                </div>
                            </div>
                            </a>
                        </div>
                        <div class="{{(auth()->guard('vendor')->check())?'col-md-12':'col-md-6'}} col-12 border-right">
                            <div class="d-inline-block form-group w-100">
                                <h4 class="mb-0">@lang('labels.backend.dashboard.recent_orders') <a
                                            class="btn btn-primary float-right"
                                            href="{{route('admin.orders.index')}}">@lang('labels.backend.dashboard.view_all')</a>
                                </h4>

                            </div>
                            <table class="table table-responsive-sm table-striped">
                                <thead>
                                <tr>
                                    <td>@lang('labels.backend.dashboard.ordered_by')</td>
                                    <td>@lang('labels.backend.dashboard.amount')</td>
                                    <td>@lang('labels.backend.dashboard.time')</td>
                                    <td>@lang('labels.backend.dashboard.view')</td>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($recent_orders) > 0)
                                    @foreach($recent_orders as $item)
                                        <tr>
                                            <td>
                                                {{isset($item->user->full_name)?$item->user->full_name:'N/A'}}
                                            </td>
                                            <td>{{$item->amount.' '.$appCurrency['symbol']}}</td>
                                            <td>{{$item->created_at->diffforhumans()}}</td>
                                            <td><a class="btn btn-sm btn-primary"
                                                   href="{{route('admin.orders.show', $item->id)}}" target="_blank"><i
                                                            class="fa fa-arrow-right"></i></a></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">@lang('labels.backend.dashboard.no_data')</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        @if(!auth()->guard('vendor')->check())
                        <div class="col-md-6 col-12">
                            <div class="d-inline-block form-group w-100">
                                <h4 class="mb-0">@lang('labels.backend.dashboard.recent_contact_requests') <a
                                            class="btn btn-primary float-right"
                                            href="{{route('admin.contact-requests.index')}}">@lang('labels.backend.dashboard.view_all')</a>
                                </h4>

                            </div>
                            <table class="table table-responsive-sm table-striped">
                                <thead>
                                <tr>
                                    <td>@lang('labels.backend.dashboard.name')</td>
                                    <td>@lang('labels.backend.dashboard.email')</td>
                                    <td>@lang('labels.backend.dashboard.message')</td>
                                    <td>@lang('labels.backend.dashboard.time')</td>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($recent_contacts) > 0)
                                    @foreach($recent_contacts as $item)
                                        <tr>
                                            <td>
                                                {{$item->name}}
                                            </td>
                                            <td>{{$item->email}}</td>
                                            <td>{{$item->message}}</td>
                                            <td>{{$item->created_at->diffforhumans()}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">@lang('labels.backend.dashboard.no_data')</td>

                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        @endif

                    @else
                        <div class="col-12">
                            <h1>@lang('labels.backend.dashboard.title')</h1>
                        </div>
                    @endif
                </div>
            </div><!--card-body-->
        </div><!--card-->
    </div><!--col-->
@endsection
