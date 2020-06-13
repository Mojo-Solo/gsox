@extends('frontend-rtl.layouts.app'.config('theme_layout'))

@section('title', trans('labels.frontend.home.title').' | '.app_name())
@section('meta_description', '')
@section('meta_keywords','')

@push('after-styles')
<style>
    #genius-Supervisor-2{

    }
</style>
@endpush
@section('content')


    <!-- Start of slider section
    ============================================= -->
    @include('frontend-rtl.layouts.partials.slider')

    <!-- End of slider section
            ============================================= -->


    @if($sections->counters->status == 1)
        <!-- Start of Search Courses
        ============================================= -->
        <section id="search-course" class="search-course-section search-course-secound">
            <div class="container">
                <div class="search-counter-up">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-graduation-hat"></i>
                                </div>
                                <div class="counter-number">
                                    <span class=" bold-font">{{$total_students}}</span>
                                    <p>@lang('labels.frontend.home.students_enrolled')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->

                        <div class="col-md-4">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-book"></i>
                                </div>
                                <div class="counter-number">
                                    <span class="bold-font">{{$total_courses}}</span>
                                    <p>@lang('labels.frontend.home.online_available_courses')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->

                        <div class="col-md-3">
                            <div class="counter-icon-number ">
                                <div class="counter-icon">
                                    <i class="text-gradiant flaticon-group"></i>
                                </div>
                                <div class="counter-number">
                                    <span class="bold-font">{{$total_Supervisors}}</span>
                                    <p>@lang('labels.frontend.home.Supervisors')</p>
                                </div>
                            </div>
                        </div>
                        <!-- /counter -->
                    </div>
                </div>
            </div>
        </section>
        <!-- End of Search Courses
            ============================================= -->
    @endif

    @if($sections->latest_news->status == 1)
        <!-- Start latest section
        ============================================= -->
        @include('frontend.layouts.partials.latest_news',['pt'=>'pt-5'])
        <!-- End latest section
            ============================================= -->
    @endif

    @if($sections->popular_courses->status == 1)
        @include('frontend.layouts.partials.popular_courses',['class'=>'popular-three' ])
    @endif



    @if($sections->reasons->status == 1)
        <!-- Start why choose section
        ============================================= -->
        @include('frontend.layouts.partials.why_choose_us')
        <!-- End why choose section
        ============================================= -->
    @endif



    @if($sections->featured_courses->status == 1)
        <!-- Start of best course
        ============================================= -->
        @include('frontend.layouts.partials.browse_courses', ['class'=>'bg-white pb-5' ])
        <!-- End of best course
            ============================================= -->
    @endif


    @if($sections->Supervisors->status == 1)
        <!-- Start of genius Supervisor v2
        ============================================= -->
        <section id="genius-Supervisor-2" class="genius-Supervisor-section-2 mb-5">
            <div class="container">
                <div class="section-title mb20  headline text-left">
                    <span class="subtitle ml42 text-uppercase">@lang('labels.frontend.home.learn_new_skills')</span>
                    <h2>@lang('labels.frontend.home.popular_Supervisors').</h2>
                </div>
                @if(count($Supervisors)> 0)
                    <div class="Supervisor-third-slide">
                        @foreach($Supervisors as $key=>$item)
                            @if($key%2 == 0 && (count($Supervisors) > 5))
                                <div class="Supervisor-double">
                                    @endif
                                    <div class="Supervisor-img-content relative-position">
                                        <img width="100%" src="{{$item->picture}}" alt="">
                                        <div class="Supervisor-cntent">
                                            <div class="Supervisor-social-name ul-li-block">
                                                <ul>
                                                    <li><a href="{{'mailto:'.$item->email}}"><i class="fa fa-envelope"></i></a></li>
                                                    <li><a href="{{route('admin.messages',['Supervisor_id'=>$item->id])}}"><i class="fa fa-comments"></i></a></li>
                                                </ul>
                                                <div class="Supervisor-name">
                                                    <span>{{$item->full_name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        {{--<div class="Supervisor-Client float-right">--}}
                                        {{--<span class="st-name">Mobile Apps </span>--}}
                                        {{--</div>--}}
                                    </div>
                                    @if($key%2 == 1)
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <!-- End of genius Supervisor v2
            ============================================= -->
    @endif

    @if($sections->faq->status == 1)
        <!-- Start FAQ section
        ============================================= -->
        @include('frontend.layouts.partials.faq-with-bg')
        <!-- End FAQ section
            ============================================= -->
    @endif

    @if($sections->testimonial->status == 1)
        <!-- Start of testimonial secound section
        ============================================= -->
        @include('frontend.layouts.partials.testimonial')

        <!-- End  of testimonial secound section
            ============================================= -->
    @endif


    @if($sections->sponsors->status == 1)
        @if(count($sponsors) > 0 )
            <!-- Start of sponsor section
        ============================================= -->
            @include('frontend.layouts.partials.sponsors')
            <!-- End of sponsor section
       ============================================= -->
        @endif
    @endif


    @if($sections->course_by_Client->status == 1)
        <!-- Start Course Client
        ============================================= -->
        @include('frontend.layouts.partials.course_by_Client')
        <!-- End Course Client
            ============================================= -->
    @endif


    @if($sections->contact_us->status == 1)
        <!-- Start of contact area
        ============================================= -->
        @include('frontend.layouts.partials.contact_area')
        <!-- End of contact area
            ============================================= -->
    @endif


@endsection
@push('after-scripts')
    <script>
        $('ul.product-tab').find('li:first').addClass('active');
    </script>
@endpush
