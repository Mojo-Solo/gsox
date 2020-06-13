@extends('frontend.layouts.app'.config('theme_layout'))
@section('title', trans('labels.frontend.cart.payment_status').' | '.app_name())

@push('after-styles')
    <style>
        input[type="radio"] {
            display: inline-block !important;
        }
    </style>
@endpush

@section('content')

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">@lang('labels.frontend.cart.your_payment_status')</h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->
    <section id="checkout" class="checkout-section">
        <div class="container">
            <div class="section-title mb45 headline text-center">
                @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>Success!</strong> @lang('labels.frontend.cart.success_message')
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    
                    <!-- <h4><a class="text-white btn gradient-bg" href="{{route('admin.dashboard')}}">@lang('labels.frontend.cart.see_more_courses')</a></h4> -->
                    <div class="best-course-area best-course-v2">
                        <div class="row">
                            @if(Session::has('courses') && !empty(Session::get('courses')))
                            <?php $courses=Session::get('courses'); ?>
                                @foreach($courses as $key => $course)

                                    <div class="col-md-4">
                                        <div class="best-course-pic-text relative-position">
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
                                                    @elseif($course->isUserEnrolled())
                                                    <?php
                                                    $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                                                    $continue_course  = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->whereNotIn('model_id',$completed_lessons)->first();
                                                    if($continue_course == null){
                                                        $continue_course = $course->courseTimeline()->where('model_type','!=','App\Models\Question')->orderby('sequence','asc')->first();
                                                    }
                                                    ?>
                                                        <a href="{{route('lessons.show',['id' => $course->id,'slug'=>$continue_course->model->slug])}}?token={{sha1(time())}}&seq={{$continue_course->sequence}}"
                       class="text-white mgradient-bg text-center text-uppercase  bold-font btn-sm">
                    @if(auth()->check() && auth()->user()->hasRole('student') && auth()->user()->chapters()->where('course_id', $course->id)->count())
                    Resume
                    @else
                    Start Course
                    @endif
                    <i class="fa fa-arow-right"></i></a>
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
                                                        <a href="{{ route('courses.show', [$course->slug]) }}">{{$course->title}}</a>
                                                    </h3>
                                                </div>
                                                <div class="course-meta">
                                                    <span class="course-Client"><a
                                                                href="{{url('courses/'.$course->Client->slug)}}"><span class="badge badge-info text-white">{{$course->Client->name}}</span></a></span>
                                                    <span class="course-author"><a href="#">{{ $course->students()->count() }}
                                                            @lang('labels.frontend.course.students')</a></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            @else
                                <h3>@lang('labels.general.no_data_available')</h3>
                        @endif

                        <!-- /course -->

                        </div>
                    </div>
                @endif
                @if(Session::has('failure'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>Error!</strong> {{session('failure')}}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <h4><a class="text-white btn gradient-bg" href="{{route('cart.index')}}">@lang('labels.frontend.cart.go_back_to_cart')</a></h4>
                @endif
                @if(Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>Error!</strong> {{session('error')}}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <h4><a class="text-white btn gradient-bg" href="{{route('cart.index')}}">@lang('labels.frontend.cart.go_back_to_cart')</a></h4>
                @endif
            </div>
        </div>
    </section>
@endsection