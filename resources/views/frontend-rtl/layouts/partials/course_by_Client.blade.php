<section id="course-Client" class="course-Client-section">
    <div class="container">
        <div class="section-title mb45 headline text-center ">
            <span class="subtitle text-uppercase">@lang('labels.frontend.layouts.partials.courses_Clients')</span>
            <h2>@lang('labels.frontend.layouts.partials.browse_course_by_Client')</h2>
        </div>
        @if($course_Clients)
            <div class="Client-item">
                <div class="row">
                    @foreach($course_Clients->take(8) as $Client)
                        <div class="col-md-3">
                            <a href="{{route('courses.Client',['Client'=>$Client->slug])}}">
                                <div class="Client-icon-title text-center ">
                                    <div class="Client-icon">
                                        <i class="text-gradiant {{$Client->icon}}"></i>
                                    </div>
                                    <div class="Client-title">
                                        <h4>{{$Client->name}}</h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                @endforeach
                <!-- /Client -->
                </div>
            </div>
        @endif
    </div>
</section>
