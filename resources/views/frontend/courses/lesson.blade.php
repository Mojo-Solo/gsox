@extends('frontend.layouts.app'.config('theme_layout'))

@push('after-styles')
    {{--<link rel="stylesheet" href="{{asset('plugins/YouTube-iFrame-API-Wrapper/css/main.css')}}">--}}
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css"/>
    <link href="{{asset('plugins/touchpdf-master/jquery.touchPDF.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/switch.css')}}">
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <style>
        #breadcrumb {
            padding-bottom: 170px;
        }
        .hide{display: none !important;}
        .breadcrumb-head {
            padding-top: 100px;
        }
        .test-form {
            color: #333333;
        }.rad-5{
            border-radius: 5px;
        }
        .switch.switch-3d {
            margin-bottom: 0px;
            vertical-align: middle;
        }
        .course-details-Client li.active {
            background: darkgrey;
        }
        .course-details-Client ul li {
            width: 100%;
        }
        .sidebar.is_stuck {
            top: 15% !important;
        }

        .course-timeline-list {
            max-height: 300px;
            overflow: scroll;
        }

        .options-list li {
            list-style-type: none;
        }

        .options-list li.correct {
            color: green;

        }

        .options-list li.incorrect {
            color: red;

        }

        .options-list li.correct:before {
            content: "\f058"; /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: green;
            margin-left: -1.3em; /* same as padding-left set on li */
            width: 1.3em; /* same as padding-left set on li */
        }

        .options-list li.incorrect:before {
            content: "\f057"; /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: red;
            margin-left: -1.3em; /* same as padding-left set on li */
            width: 1.3em; /* same as padding-left set on li */
        }

        .options-list li:before {
            content: "\f111"; /* FontAwesome Unicode */
            font-family: 'Font Awesome\ 5 Free';
            display: inline-block;
            color: black;
            margin-left: -1.3em; /* same as padding-left set on li */
            width: 1.3em; /* same as padding-left set on li */
        }

        .touchPDF {
            border: 1px solid #e3e3e3;
        }

        .touchPDF > .pdf-outerdiv > .pdf-toolbar {
            height: 0;
            color: black;
            padding: 5px 0;
            text-align: right;
        }

        .pdf-tabs {
            width: 100% !important;
        }

        .pdf-outerdiv {
            width: 100% !important;
            left: 0 !important;
            padding: 0px !important;
            transform: scale(1) !important;
        }

        .pdf-viewer {
            left: 0px;
            width: 100% !important;
        }

        .pdf-drag {
            width: 100% !important;
        }

        .pdf-outerdiv {
            left: 0px !important;
        }

        .pdf-outerdiv {
            padding-left: 0px !important;
            left: 0px;
        }

        .pdf-toolbar {
            left: 0px !important;
            width: 99% !important;
            height: 30px;
        }

        .pdf-viewer {
            box-sizing: border-box;
            left: 0 !important;
            margin-top: 10px;
        }

        .pdf-title {
            display: none !important;
        }
       
        @media screen  and  (max-width: 768px) {

        }

    </style>
    @if(!empty($lesson) && get_class($lesson)=="App\Models\Test")
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css">
    @endif
@endpush

@section('content')
<?php 
    $forward=1;
    if(!empty($lesson) && get_class($lesson)=="App\Models\Test" && !empty($test_result) && count($lesson->questions) && (($test_result->test_result/count($lesson->questions))*100 >= $lesson->passing_percentage) ) {
        $forward=1;
        (!empty($test_result)) ? dump($test_result->test_result) : '';
    }if(!empty($lesson) && get_class($lesson)=="App\Models\Test" && !empty($test_result) && count($lesson->questions) && (($test_result->test_result/count($lesson->questions))*100 < $lesson->passing_percentage) ) {
        $forward=0;
        (!empty($test_result)) ? dump($test_result->test_result) : '';
        

    }elseif(!empty($lesson) && get_class($lesson)=="App\Models\Test" && empty($test_result)) {
        $forward=0;
        (!empty($test_result)) ? dump($test_result->test_result) : '';
      
    }
    $course_timelines=\App\Models\CourseTimeline::where('course_id',$course->id)->distinct('model_id')->orderBy('sequence','asc')->get();
    $item=(isset($course_timelines))?$course_timelines[0]:$course->courseTimeline()->distinct('model_id')->orderBy('sequence')->get()[0];
    $link='';
    $activelessons=0;
    $totallessons=0;
    if(in_array($item->model->id,$completed_lessons)) {
        $link=route('lessons.show',['id' => $course->id,'slug'=>$item->model->slug]);
    }



    function check_url($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        return $headers['http_code'];
    }
?>

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">
                        <span>{{$course->title}}</span>
                    </h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->


    <!-- Start of course details section
        ============================================= -->
    <section id="course-details" class="course-details-section">
        <div class="container ">
            <div class="row main-content">
                <div class="col-md-9">
                    @if(session()->has('success'))
                        <div class="alert alert-dismissable alert-success fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{session('success')}}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert alert-dismissable alert-danger fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{session('error')}}
                        </div>
                    @endif

                    @if(!empty($lesson))
                    <div class="course-details-item border-bottom-0 mb-0">
                        @if($lesson->lesson_image != "")
                            <div class="course-single-pic mb30">
                                <img src="{{asset('storage/uploads/'.$lesson->lesson_image)}}"
                                     alt="">
                            </div>
                        @endif
                        @if(check_url($lesson->topic_image)=='200')
                            <div class="course-single-pic mb30">
                                <img src="{{$lesson->topic_image}}"
                                     alt="">
                            </div>
                        @elseif(isset($lesson->topic_image) && !empty($lesson->topic_image))
                            <div class="course-single-pic mb30">
                                <img src="{{asset('storage/uploads/'.$lesson->topic_image)}}"
                                     alt="">
                            </div>
                        @endif

                        @if ($test_exists && is_array($lesson->questions))
                            <div class="course-single-text">
                                <div class="course-title mt10 headline relative-position">
                                    <h3>
                                        <b>@lang('labels.frontend.course.test')
                                            : {{(isset($lesson->title))?$lesson->title:$lesson->question}}</b>
                                    </h3>
                                    @if(isset($lesson->question_image) && is_file('public/storage/uploads/'.$lesson->question_image))
                                    <img src="{{url('public/storage/uploads/'.$lesson->question_image)}}" />
                                    @endif
                                </div>
                                <div class="course-details-content">
                                    <p> {!! $lesson->full_text !!} </p>
                                </div>
                            </div>
                            <hr/>

                            @if($test_exists && is_null($test_result))
                            <?php $forward=0; ?>
                            @endif

                            @if (!is_null($test_result))
                                <div class="alert alert-info">@lang('labels.frontend.course.your_test_score')
                                    : {{ $test_result->test_result }}</div>
                                @if(config('retest'))
                                    <form action="{{route('lessons.retest',[$test_result->test->slug])}}" method="post">
                                        @csrf
                                        <input type="hidden" name="result_id" value="{{$test_result->id}}">
                                        <button type="submit" class="btn gradient-bg font-weight-bold text-white"
                                                href="">
                                            @lang('labels.frontend.course.give_test_again')
                                        </button>
                                    </form>
                                @endif
                                @if(is_array($lesson->lesson) && $lesson->questions)
                                    <hr>

                                    @foreach ($lesson->randomquestions() as $question)
                                        <h4 class="mb-0">{{ $loop->iteration }}
                                            . {{ $question->question }} @if(!$question->isAttempted($test_result->id))
                                                <?php $forward=0; ?>
                                                <small class="badge badge-danger"> @lang('labels.frontend.course.not_attempted')</small> @endif
                                        </h4>
                                        @if(is_file('public/storage/uploads/'.$question->question_image))
                                        <img src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                        @endif
                                        <br/>
                                        <ul class="options-list pl-4">
                                            @foreach ($question->qes_options() as $option)
                                                <li class="@if(($option->answered($test_result->id) != null && $option->answered($test_result->id) == 1)) correct @elseif($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2) <?php $forward=0; ?> incorrect  @endif @if($option->correct == 1) correct @endif"> {{ $option->option_text }}

                                                    @if($option->correct == 1 && $option->explanation != null)
                                                        <!-- <p class="text-dark">
                                                            <b>@lang('labels.frontend.course.explanation')</b><br>
                                                            {{$option->explanation}}
                                                        </p> -->
                                                    @endif
                                                </li>

                                            @endforeach
                                        </ul>
                                        <br/>
                                    @endforeach
                                    @if($forward==0)
                                    <center>
                                        <form method="post" id="reviewForm" action="{{url('review-course')}}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$lesson->id}}">
                                            <input type="hidden" name="cid" value="{{$course->id}}">
                                            <button class="btn gradient-bg font-weight-bold text-white" type="submit">Review Topic</button>
                                        </form>
                                    </center>
                                    @endif
                                @else
                                    <h3>@lang('labels.general.no_data_available')</h3>

                                @endif
                            @else
                                <div class="test-form">
                                    @if(is_array($lesson->lesson) && $lesson->questions)
                                        <form action="{{ route('lessons.test', [$lesson->slug]) }}" method="post">
                                            {{ csrf_field() }}
                                            @foreach ($lesson->randomquestions() as $question)
                                                <h4 class="mb-0">{{ $loop->iteration }}. {{ $question->question }}</h4>
                                                @if(is_file('public/storage/uploads/'.$question->question_image))
                                                <img src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                                @endif
                                                <br/>
                                                @foreach ($question->randomoptions() as $option)
                                                    <div class="radio">
                                                        <label>
                                                            <input type="radio" name="questions[{{ $question->id }}]"
                                                                   value="{{ $option->id }}"/>
                                                            <span class="cr"><i class="cr-icon fa fa-circle"></i></span>
                                                            {{ $option->option_text }}<br/>
                                                        </label>
                                                    </div>
                                                @endforeach
                                                <br/>
                                            @endforeach
                                            <input class="btn gradient-bg text-white font-weight-bold" type="submit"
                                                   value=" @lang('labels.frontend.course.submit_results') "/>
                                                   @if(Session::has('res'))
                                                   <form method="post" id="reviewForm" action="{{url('review-course')}}">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$lesson->id}}">
                                                    <input type="hidden" name="cid" value="{{$course->id}}">
                                                    <button class="btn gradient-bg font-weight-bold text-white" type="submit">Review Topic</button>
                                                </form>
                                                   @endif
                                        </form>
                                    @else
                                        <h3>@lang('labels.general.no_data_available')</h3>

                                    @endif
                                </div>
                            @endif
                            <hr/>
                        @elseif(isset($lesson->question))
                        <?php $question=$lesson; ?>
                            @if($test_exists && (is_null($test_result)))
                            <?php $forward=0; ?>
                            @endif
                            @if (!is_null($test_result))
                                @if(config('retest'))
                                    <form action="{{ route('lessons.requestion', [$course->id,$lesson->id,sha1(time())]) }}" method="post">
                                        @csrf
                                        <input type="hidden" name="result_id" value="{{$test_result->id}}">
                                        <button type="submit" class="btn gradient-bg font-weight-bold text-white"
                                                href="">Give Answer Again</button>
                                    </form>
                                @endif
                                    <hr>
                                        <h4 class="mb-0">{{ $question->question }} @if(!$question->isAttempted($test_result->id))
                                                <?php $forward=0; ?>
                                                <small class="badge badge-danger"> @lang('labels.frontend.course.not_attempted')</small> @endif
                                        </h4>
                                        @if(is_file('public/storage/uploads/'.$question->question_image))
                                        <img src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                        @endif
                                        <br/>
                                        <ul class="options-list pl-4">
                                            @foreach ($question->qes_options() as $option)
                                                <li class="@if(($option->answered($test_result->id) != null && $option->answered($test_result->id) == 1)) correct @elseif($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2) <?php $forward=0; ?> incorrect  @endif @if($option->correct == 1) correct @endif"> {{ $option->option_text }}

                                                    @if($option->correct == 1 && $option->explanation != null)
                                                        <!-- <p class="text-dark">
                                                            <b>@lang('labels.frontend.course.explanation')</b><br>
                                                            {{$option->explanation}}
                                                        </p> -->
                                                    @endif
                                                </li>

                                            @endforeach
                                        </ul>
                                        <br/>
                                    @if($forward==0)
                                        <?php 
                                            $item=(isset($course_timelines))?$course_timelines[0]:$course->courseTimeline()->distinct('model_id')->orderBy('sequence')->get()[0];
                                            $link='';
                                            if(in_array($item->model->id,$completed_lessons)) {
                                                if(isset($lesson->review_topic_id) && !empty($lesson->review_topic_id)) {
                                                    $topic=App\Models\Topic::find($lesson->review_topic_id);
                                                    if($topic) {
                                                        $link=route('lessons.show',['id' => $course->id,'slug'=>$topic->slug]);
                                                    } else {
                                                        $link=route('lessons.show',['id' => $course->id,'slug'=>$item->model->slug]);
                                                    }
                                                } else {
                                                    $link=route('lessons.show',['id' => $course->id,'slug'=>$item->model->slug]);
                                                }
                                            }
                                         ?>
                                    <center>
                                        <form method="post" id="reviewForm" action="{{url('review-course')}}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$lesson->id}}">
                                            <input type="hidden" name="cid" value="{{$course->id}}">
                                            <button class="btn gradient-bg font-weight-bold text-white" type="submit">Review Topic</button>
                                        </form>
                                    </center>
                                    @endif
                            @else
                            <div class="course-single-text">
                                <div class="course-details-content">
                                    {!! $lesson->full_text !!}
                                </div>
                            </div>
                            <div class="test-form">
                                <form action="{{ route('lessons.question', [$course->id,$lesson->id,sha1(time())]) }}" method="post">
                                    {{ csrf_field() }}
                                    <h4 class="mb-0">{{ $lesson->question }}</h4>
                                    @if(is_file('public/storage/uploads/'.$lesson->question_image))
                                        <img src="{{url('public/storage/uploads/'.$lesson->question_image)}}" />
                                        @endif
                                    <br/>
                                    <?php
                                        $orders=array();
                                    ?>
                                    @foreach ($lesson->randomoptions() as $option)
                                    <?php array_push($orders, $option->id); ?>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="questions[{{ $lesson->id }}]"
                                                       value="{{ $option->id }}"/>
                                                <span class="cr"><i class="cr-icon fa fa-circle"></i></span>
                                                {{ $option->option_text }}<br/>
                                            </label>
                                        </div>
                                    @endforeach
                                    <input type="hidden" name="orders" value="{{implode(',',$orders)}}">
                                    <br/>
                                    <input class="btn gradient-bg text-white font-weight-bold" type="submit"
                                           value=" @lang('labels.frontend.course.submit_results') "/>
                                           @if(Session::has('res'))
                                           <form method="post" id="reviewForm" action="{{url('review-course')}}">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$lesson->id}}">
                                                <input type="hidden" name="cid" value="{{$course->id}}">
                                                <button class="btn gradient-bg font-weight-bold text-white" type="submit">Review Topic</button>
                                            </form>
                                           @endif
                                </form>
                            </div>
                            @endif
                            <br/>
                        @elseif(get_class($lesson)=="App\Models\Test")
                            <div class="test-form">
                                @if(!empty($lesson->questions))
                                @if(isset($test_result) && $forward==1 && $lesson->show_answers)
                                <div class="alert alert-dismissable alert-success fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    Test Passed! Score {{$test_result->test_result}}
                                </div>
                                <h3>{{$lesson->title}}</h3>
                                <p>{{$lesson->description}}</p>
                                <hr>
                                    @foreach ($lesson->questions as $key => $question)
                                        <h4 class="mb-0">{{ $key+1 }}. {{ $question->question }}</h4>
                                        @if(is_file('public/storage/uploads/'.$question->question_image))
                                        <img src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                        @endif
                                        <br/>
                                        <ul class="options-list pl-4">
                                            @foreach ($question->qes_options() as $option)
                                                <li class="@if(($option->answered($test_result->id) != null && $option->answered($test_result->id) == 1)) correct @elseif($option->answered($test_result->id) != null && $option->answered($test_result->id) == 2) incorrect  @endif @if($option->correct == 1) correct @endif"> {{ $option->option_text }}

                                                    @if($option->correct == 1 && $option->explanation != null)
                                                        <!-- <p class="text-dark">
                                                            <b>@lang('labels.frontend.course.explanation')</b><br>
                                                            {{$option->explanation}}
                                                        </p> -->
                                                    @endif
                                                </li>

                                            @endforeach
                                        </ul>
                                    @endforeach
                                @else
                                <h3>{{$lesson->title}}</h3>
                                <p>{{$lesson->description}}</p>
                                <hr>
                                    <form class="animate-form" action="{{ route('lessons.test', [$lesson->slug]) }}" id="testform" method="post">
                                        {{ csrf_field() }}
                                        @foreach ($lesson->randomquestions() as $question)
                                        <div class="form-group has-feedback" data-id="{{$question->id}}" id="qes{{$question->id}}">
                                            <h4 class="mb-0">{{ $loop->iteration }}. {{ $question->question }}</h4>
                                            @if(is_file('public/storage/uploads/'.$question->question_image))
                                            <img src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                            @endif
                                            <br/>
                                            @foreach ($question->randomoptions() as $option)
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" id="question{{$question->id}}" name="questions[{{ $question->id }}]" value="{{ $option->id }}" onchange="remValidate('{{ $question->id }}')" />
                                                        <span class="cr"><i class="cr-icon fa fa-circle"></i></span>
                                                        {{ $option->option_text }}<br/>
                                                    </label>
                                                </div>
                                            @endforeach
                                            <br/>
                                        </div>
                                        @endforeach
                                        <input class="btn gradient-bg text-white font-weight-bold" type="submit"
                                               value=" @lang('labels.frontend.course.submit_results') "/>
                                               @if(Session::has('res'))
                                               <form method="post" id="reviewForm" action="{{url('review-course')}}">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$lesson->id}}">
                                                <input type="hidden" name="cid" value="{{$course->id}}">
                                                <button class="btn gradient-bg font-weight-bold text-white" type="submit">Review Topic</button>
                                            </form>
                                               @endif
                                    </form>
                                    @endif
                                @else
                                    <h3>@lang('labels.general.no_data_available')</h3>

                                @endif
                            </div>
                        @else
                            <div class="course-single-text">
                                <div class="course-title mt10 headline relative-position">
                                    <h3>
                                        <b>{{(isset($lesson->title))?$lesson->title:$lesson->question}}</b>
                                    </h3>
                                    @if(isset($lesson->question) && is_file('public/storage/uploads/'.$lesson->question_image))
                                    <img src="{{url('public/storage/uploads/'.$lesson->question_image)}}" />
                                    @endif
                                </div>
                                <div class="course-details-content">
                                    {!! $lesson->full_text !!}
                                </div>
                            </div>
                        @endif

                        @if($lesson->mediaPDF)
                            <div class="course-single-text mb-5">
                                {{--<iframe src="{{asset('storage/uploads/'.$lesson->mediaPDF->name)}}" width="100%"--}}
                                {{--height="500px">--}}
                                {{--</iframe>--}}
                                <div id="myPDF"></div>

                            </div>
                        @endif


                        @if($lesson->mediaVideo && $lesson->mediavideo->count() > 0)
                            <div class="course-single-text">
                                @if($lesson->mediavideo != "")
                                    <div class="course-details-content mt-3">
                                        <div class="video-container mb-5" data-id="{{$lesson->mediavideo->id}}">
                                            @if($lesson->mediavideo->type == 'youtube')


                                                <div id="player" class="js-player" data-plyr-provider="youtube"
                                                     data-plyr-embed-id="{{$lesson->mediavideo->file_name}}"></div>
                                            @elseif($lesson->mediavideo->type == 'vimeo')
                                                <div id="player" class="js-player" data-plyr-provider="vimeo"
                                                     data-plyr-embed-id="{{$lesson->mediavideo->file_name}}"></div>
                                            @elseif($lesson->mediavideo->type == 'upload')
                                                <video poster="" id="player" class="js-player" playsinline controls>
                                                    <source src="{{$lesson->mediavideo->url}}" type="video/mp4"/>
                                                </video>
                                            @elseif($lesson->mediavideo->type == 'embed')
                                                {!! $lesson->mediavideo->url !!}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($lesson->mediaAudio)
                        <div class="card p-2 hide">
                            <h6 class="card-title">Audio</h6>
                            <div class="course-single-text mb-5" id="audio_div">
                                <audio id="audioPlayer" {{(Session::has("audiostatus") && Session::get("audiostatus")==1)?'autoplay':''}}>
                                    <source src="{{$lesson->mediaAudio->url}}"/>
                                </audio>
                            </div>
                        </div>
                        @elseif(check_url($lesson->topic_audio)=='200')
                        <div class="card p-2 hide">
                            <h6 class="card-title">Audio</h6>
                            <div class="course-single-text mb-5">
                                <audio id="audioPlayer" {{(Session::has("audiostatus") && Session::get("audiostatus")==1)?'autoplay':''}}>
                                    <source src="{{$lesson->topic_audio}}" type="audio/mp3"/>
                                </audio>
                            </div>
                        </div>
                        @elseif(check_url(asset('storage/uploads/'.$lesson->topic_audio))=='200')
                            <div class="card p-2 hide">
                                <h6 class="card-title">Audio</h6>
                                <div class="course-single-text mb-5">
                                    <audio id="audioPlayer" {{(Session::has("audiostatus") && Session::get("audiostatus")==1)?'autoplay':''}}>
                                        <source src="{{asset('storage/uploads/'.$lesson->topic_audio)}}" type="audio/mp3"/>
                                    </audio>
                                </div>
                            </div>
                        @endif

                        @if(($lesson->downloadableMedia != "") && ($lesson->downloadableMedia->count() > 0))
                            <div class="course-single-text mt-4 px-3 py-1 gradient-bg text-white">
                                <div class="course-title mt10 headline relative-position">
                                    <h4 class="text-white">
                                        @lang('labels.frontend.course.download_files')
                                    </h4>
                                </div>

                                @foreach($lesson->downloadableMedia as $media)
                                    <div class="course-details-content text-white">
                                        <p class="form-group">
                                            <a href="{{ route('download',['filename'=>$media->name,'lesson'=>$lesson->id]) }}"
                                               class="text-white font-weight-bold"><i
                                                        class="fa fa-download"></i> {{ $media->name }}
                                                        <?php 
                                                        function convertToReadableSize($size)
                                                        {
                                                          $base = log($size) / log(1024);
                                                          $suffix = array("B", "KB", "MB", "GB", "TB");
                                                          $f_base = floor($base);
                                                          return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
                                                        }
                                                        ?>
                                                ({{ convertToReadableSize($media->size)}}
                                                )</a>
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <!-- /course-details -->
                    @else
                    <h4 class="text-danger">{{ucfirst($item_type)}} Deleted</h4>
                    @endif

                    <!-- /market guide -->

                    <!-- /review overview -->
                </div>
          
                @if($forward==1 || get_class($lesson) != "App\Models\Test")
                <div class="col-md-3">
                    <div id="sidebar" class="sidebar">
                        <div class="course-details-Client ul-li">
                            @if(!empty($lesson))
                            @if(check_url($lesson->topic_audio)=='200')
                            <label class="label-switch switch-info">
                                <input type="checkbox" class="switch-square switch-bootstrap status" name="audiostatus" id="audiostatus" value="0">
                                <span class="lable"></span>
                                <h7>Audio</h7>
                            </label>
                            @elseif(check_url(asset('storage/uploads/'.$lesson->topic_audio))=='200')
                            <label class="label-switch switch-info">
                                <input type="checkbox" class="switch-square switch-bootstrap status" name="audiostatus" id="audiostatus" value="0">
                                <span class="lable"></span>
                                <h7>Audio</h7>
                            </label>
                            @endif
                            @endif
                            <p id="nextButton">
                                @if(isset($forward) && $forward==1)
                                    @if($next_lesson)
                                        @if((int)config('lesson_timer') == 1 && !empty($lesson) && $lesson->isCompleted() )
                                            <a class="btn btn-block gradient-bg font-weight-bold text-white btn-lg"
                                               href="{{ route('lessons.show', [$next_lesson->course_id, (isset($next_lesson->model->slug))?$next_lesson->model->slug:$next_lesson->model_id]) }}/{{$next_lesson->model_id.'/'.sha1(time())}}?seq={{$next_lesson->sequence}}">@lang('labels.frontend.course.next')
                                                <i class='fa fa-angle-double-right'></i> </a>
                                        @else
                                            <a class="btn btn-block gradient-bg font-weight-bold text-white btn-lg"
                                               href="{{ route('lessons.show', [$next_lesson->course_id, (isset($next_lesson->model->slug))?$next_lesson->model->slug:$next_lesson->model_id]) }}/{{$next_lesson->model_id.'/'.sha1(time())}}?seq={{$next_lesson->sequence}}">@lang('labels.frontend.course.next')
                                                <i class='fa fa-angle-double-right'></i> </a>
                                        @endif
                                    @endif
                                @else
                                <button type="button" class="btn btn-block btn-disabled font-weight-bold text-white" disabled="">@lang('labels.frontend.course.next')
                                                <i class='fa fa-angle-double-right'></i></button>
                                @endif
                            </p>
                            @if($previous_lesson && isset($forward) && $forward==1)
                                <p><a class="btn btn-block gradient-bg font-weight-bold text-white"
                                      href="{{ route('lessons.show', [$previous_lesson->course_id, (isset($previous_lesson->model->slug))?$previous_lesson->model->slug:$previous_lesson->model_id]) }}/{{$previous_lesson->model_id.'/'.sha1(time())}}?seq={{$previous_lesson->sequence}}"><i
                                                class="fa fa-angle-double-left"></i>
                                        @lang('labels.frontend.course.prev')</a></p>
                            @else
                            <button type="button" class="btn btn-block btn-disabled font-weight-bold text-white" disabled="">@lang('labels.frontend.course.prev')
                                                <i class='fa fa-angle-double-left'></i></button>
                            @endif
                            <?php $comp=($activelessons)?intval(($totallessons/$activelessons)*100):$course->progress(); ?>
                            @if($comp == 100)
                                @if(($logged_in_user->hasRole('Supervisor') || $logged_in_user->isAdmin()))
                                <button class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                id="finish" disabled>Course Finished</button>
                                @else
                                @if(!$course->isUserCertified())
                                    <form method="post" action="{{route('admin.certificates.generate')}}">
                                        @csrf
                                        <input type="hidden" value="{{$course->id}}" name="course_id">
                                        <button class="btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold"
                                                id="finish">@lang('labels.frontend.course.finish_course')</button>
                                    </form>
                                @else
                                    <div class="alert alert-success">
                                        @lang('labels.frontend.course.certified')
                                    </div>
                                @endif
                                @endif
                            @endif

                            @if($forward==1)
                            <span class="float-none">@lang('labels.frontend.course.course_timeline')</span>
                            <ul class="course-timeline-list">
                                <?php $active=0; ?>
                                @foreach($course_timelines as $key=>$item)
                                    @if($item->model_type != 'App\Models\Question' && $item->model && $item->model->published == 1)
                                        {{--@php $key++; @endphp--}}

                                        <?php $totallessons++; ?>
                                        <li class="@if(!empty($lesson) && $lesson->id == $item->model->id && $active==0 && $sequence==$item->sequence) active <?php $active=1; ?> @endif ">
                                            <a @if(in_array($item->model->id,$completed_lessons))href="{{route('lessons.show',['id' => $course->id,'slug'=>$item->model->slug,])}}?token={{sha1(time())}}&seq={{$item->sequence}}" @endif>
                                                {{$item->model->title}}
                                                @if($item->model_type == 'App\Models\Test')
                                                    <p class="mb-0 text-primary">
                                                        - @lang('labels.frontend.course.test')</p>
                                                @endif
                                                @if(in_array($item->model->id,$completed_lessons)) <i
                                                        class="fa text-success float-right fa-check-square"></i>
                                                        <?php $activelessons++; ?>
                                                         @endif
                                            </a>
                                        </li>
                                    @endif
                                    @if($item->model_type=="App\Models\Question")
                                        {{--@php $key++; @endphp--}}
                                        <?php $totallessons++; ?>
                                        <li class="@if(!empty($lesson) && $lesson->id == $item->model->id && $active==0 && $sequence==$item->sequence) active <?php $active=1; ?> @endif ">
                                            <a @if(in_array($item->model->id,$completed_lessons)) href="{{ route('lessons.show', [$course->id, ($item->model->slug)?$item->model->slug:$item->model_id]) }}/{{$item->model_id.'/'.sha1(time())}}?seq={{$item->sequence}}"@endif>
                                                {{$item->model->title}}
                                                @if(in_array($item->model->id,$completed_lessons)) <i
                                                        class="fa text-success float-right fa-check-square"></i> <?php $activelessons++; ?> @endif
                                                <p class="mb-0 text-primary">
                                                        - @lang('labels.backend.faqs.fields.question')</p>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        <div class="couse-feature ul-li-block">
                            <ul>
                                <li>@lang('labels.frontend.course.chapters')
                                    <span> {{$course->chapterCount()}} </span></li>
                                <li>@lang('labels.frontend.course.Client') <span><a
                                                href="{{route('courses.Client',['Client'=>$course->Client->slug])}}"
                                                target="_blank">{{$course->Client->name}}</a> </span></li>
                                <!-- <li>@lang('labels.frontend.course.author') <span>

                   @foreach($course->Supervisors as $key=>$Supervisor)
                                            @php $key++ @endphp
                                            <a href="{{route('Supervisors.show',['id'=>$Supervisor->id])}}" target="_blank">
                           {{$Supervisor->full_name}}@if($key < count($course->Supervisors )), @endif
                       </a>
                                        @endforeach
                                    </span>
                                </li> -->
                                <li>@lang('labels.frontend.course.progress') <span> <b> {{ $course->progress()  }}
                                            % @lang('labels.frontend.course.completed')</b></span></li>
                            </ul>

                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    <!-- End of course details section
    ============================================= -->

@endsection

@push('after-scripts')
    {{--<script src="//www.youtube.com/iframe_api"></script>--}}
    <script src="{{asset('plugins/sticky-kit/sticky-kit.js')}}"></script>
    <script src="https://cdn.plyr.io/3.5.3/plyr.polyfilled.js"></script>
    <script src="{{asset('plugins/touchpdf-master/pdf.compatibility.js')}}"></script>
    <script src="{{asset('plugins/touchpdf-master/pdf.js')}}"></script>
    <script src="{{asset('plugins/touchpdf-master/jquery.touchSwipe.js')}}"></script>
    <script src="{{asset('plugins/touchpdf-master/jquery.touchPDF.js')}}"></script>
    <script src="{{asset('plugins/touchpdf-master/jquery.panzoom.js')}}"></script>
    <script src="{{asset('plugins/touchpdf-master/jquery.mousewheel.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>


    <script>
        $('.footer-content').remove();
        $('.footer-social-subscribe').remove();
        var storedDuration = 0;
        var storedLesson;
        $.fn.timedDisable = function(time) {
          if (time == null) {
            time = 1;
          }
          var seconds = Math.ceil(time);
          var url=$(this).attr('href');
          return $(this).each(function() {
            $(this).attr('disabled', true);
            $(this).addClass('disabled');
            $(this).attr('href','javascript:void(0)');
            var disabledElem = $(this);
            var originalText = this.innerHTML;
            disabledElem.html(originalText + ' (' + seconds + ')');
            var interval = setInterval(function() {
                seconds = seconds - 1;
              disabledElem.html(originalText + ' (' + seconds + ')');
              if (seconds === 0) {
                disabledElem.attr('disabled',false)
                  .html(originalText);
                  disabledElem.attr('href',url);
                  disabledElem.removeClass('disabled');
                clearInterval(interval);
              }
            }, 1000);
          });
        };
        if('{{$item_type}}'!='question' && '{{$item_type}}'!='test') {
            $('#nextButton >a').timedDisable(parseInt('{{config("course_lesson_timer")}}'));
        }
        storedDuration = Cookies.get("duration_" + "{{auth()->user()->id}}" + "_" + "{{($lesson)?$lesson->id:0}}" + "_" + "{{$course->id}}");
        storedLesson = Cookies.get("lesson" + "{{auth()->user()->id}}" + "_" + "{{($lesson)?$lesson->id:0}}" + "_" + "{{$course->id}}");
        var user_lesson;

        if (parseInt(storedLesson) != parseInt("{{($lesson)?$lesson->id:0}}")) {
            Cookies.set('lesson', parseInt('{{($lesson)?$lesson->id:0}}'));
        }
        const player2 = new Plyr('#audioPlayer');
        $('#audiostatus').on('change',function(){
        if($(this).is(":checked")) {
            player2.play();
            // $.ajax({
            //     url: "{{route('audiostatus')}}",
            //     method: "POST",
            //     data: {
            //         "_token": "{{ csrf_token() }}",
            //         'audiostatus': 1
            //     }
            // });
        } else {
            player2.stop();
            // $.ajax({
            //     url: "{{route('audiostatus')}}",
            //     method: "POST",
            //     data: {
            //         "_token": "{{ csrf_token() }}",
            //         'audiostatus': 0
            //     }
            // });
        }
      });


                @if(!empty($lesson) && $lesson->mediaVideo && $lesson->mediaVideo->type != 'embed')
        var current_progress = 0;


        @if(!empty($lesson) && $lesson->mediaVideo->getProgress(auth()->user()->id) != "")
            current_progress = "{{$lesson->mediaVideo->getProgress(auth()->user()->id)->progress}}";
        @endif

        @if(!empty($lesson) && $lesson->mediaPDF)
        $(function () {
            $("#myPDF").pdf({
                source: "{{asset('storage/uploads/'.$lesson->mediaPDF->name)}}",
                loadingHeight: 800,
                loadingWidth: 800,
                loadingHTML: ""
            });

        });
                @endif
        const player = new Plyr('#player');
        duration = 10;
        var progress = 0;
        var video_id = $('#player').parents('.video-container').data('id');
        player.on('ready', event => {
            player.currentTime = parseInt(current_progress);
            duration = event.detail.plyr.duration;


            if (!storedDuration || (parseInt(storedDuration) === 0)) {
                Cookies.set("duration_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}", duration);
            }

        });

        {{--if (!storedDuration || (parseInt(storedDuration) === 0)) {--}}
        {{--Cookies.set("duration_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}", player.duration);--}}
        {{--}--}}


        setInterval(function () {
            player.on('timeupdate', event => {
                if ((parseInt(current_progress) > 0) && (parseInt(current_progress) < parseInt(event.detail.plyr.currentTime))) {
                    progress = current_progress;
                } else {
                    progress = parseInt(event.detail.plyr.currentTime);
                }
            });

            saveProgress(video_id, duration, parseInt(progress));
        }, 10000);


        function saveProgress(id, duration, progress) {
            $.ajax({
                url: "{{route('update.videos.progress')}}",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'video': parseInt(id),
                    'duration': parseInt(duration),
                    'progress': parseInt(progress)
                },
                success: function (result) {
                    if (progress === duration) {
                        location.reload();
                    }
                }
            });
        }


        $('#notice').on('hidden.bs.modal', function () {
            location.reload();
        });

        @endif

        $("#sidebar").stick_in_parent();


        @if((int)config('lesson_timer') != 0)
        //Next Button enables/disable according to time

        var readTime, totalQuestions, testTime;
        user_lesson = Cookies.get("user_lesson_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}");

        @if ($test_exists )
            totalQuestions = '{{(is_array($lesson->questions))?count($lesson->questions):1}}'
        readTime = parseInt(totalQuestions) * 30;
        @elseif (isset($lesson->question) )
            totalQuestions = 1;
            readTime = parseInt(totalQuestions) * 30;
        @else
            readTime = parseInt("{{$lesson->readTime()}}") * 60;
        @endif

    @if(!$lesson->isCompleted())
            storedDuration = Cookies.get("duration_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}");
        storedLesson = Cookies.get("lesson" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}");


        var totalLessonTime = readTime + (parseInt(storedDuration) ? parseInt(storedDuration) : 0);
        var storedCounter = (Cookies.get("storedCounter_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}")) ? Cookies.get("storedCounter_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}") : 0;
        var counter;
        if (user_lesson) {
            if (user_lesson === 'true') {
                counter = 1;
            }
        } else {
            if ((storedCounter != 0) && storedCounter < totalLessonTime) {
                counter = storedCounter;
            } else {
                counter = totalLessonTime;
            }
        }
        counter=0;
        var interval = setInterval(function () {
            counter--;
            // Display 'counter' wherever you want to display it.
            if (counter >= 0) {
                // Display a next button box
                $('#nextButton').html("<a class='btn btn-block bg-danger font-weight-bold text-white' href='#'>@lang('labels.frontend.course.next') (in " + counter + " seconds)</a>")
                Cookies.set("duration_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}", counter);

            }
            if (counter === 0) {
                Cookies.set("user_lesson_" + "{{auth()->user()->id}}" + "_" + "{{$lesson->id}}" + "_" + "{{$course->id}}", 'true');
                Cookies.remove('duration');

                @if ($test_exists && (is_null($test_result)))
                $('#nextButton').html("<a class='btn btn-block bg-danger font-weight-bold text-white' href='#'>@lang('labels.frontend.course.complete_test')</a>")
                @else
                @if($next_lesson)
                $('#nextButton').html("<a class='btn btn-block gradient-bg font-weight-bold text-white'" +
                    " href='{{ route('lessons.show', [$next_lesson->course_id, $next_lesson->model->slug]) }}?seq={{$next_lesson->sequence}}'>@lang('labels.frontend.course.next')<i class='fa fa-angle-double-right'></i> </a>");
                @else
                $('#nextButton').html("<form method='post' action='{{route("admin.certificates.generate")}}'>" +
                    "<input type='hidden' name='_token' id='csrf-token' value='{{ Session::token() }}' />" +
                    "<input type='hidden' value='{{$course->id}}' name='course_id'> " +
                    "<button class='btn btn-success btn-block text-white mb-3 text-uppercase font-weight-bold' id='finish'>@lang('labels.frontend.course.finish_course')</button></form>");

                @endif

                @if(!$lesson->isCompleted())
                courseCompleted("{{$lesson->id}}", "{{get_class($lesson)}}");
                @endif
                @endif
                clearInterval(counter);
            }
        }, 1000);

        @endif
        @endif

        function courseCompleted(id, type) {
            $.ajax({
                url: "{{route('update.course.progress')}}",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'model_id': parseInt(id),
                    'model_type': type,
                },
            });
        }
        // $('#main-menu').attr('class','main-menu-container menu-bg-overlay');
        // $('.course-timeline-list > li.actives').each(function(i,item){
        //     if($('.course-timeline-list > li.actives').length==(i+1)){
        //         $(this).removeClass('actives');
        //         $(this).addClass('active');
        //     } else {
        //         $(this).removeClass('actives');
        //     }
        // });
        $('.course-timeline-list > li').each(function(i,item){
            if($(this).hasClass('active')) {
                $('.course-timeline-list').scrollTop($('.course-timeline-list li:nth-child('+(i-6)+')').position().top);
                // $('.course-timeline-list').animate({
                //      scrollTop: $('.course-timeline-list li:nth-child('+(i-6)+')').position().top
                // }, 'slow');
            }
        });
    
    $(function() {
      $(document).on('click', '.switch-input', function (e) {
//              e.preventDefault();
            var content = $(this).parents('.checkbox').siblings('.switch-content');
            if (content.hasClass('d-none')) {
                $(this).attr('checked', 'checked');
                content.find('input').attr('required', true);
                content.removeClass('d-none');
            } else {
                content.addClass('d-none');
                content.find('input').attr('required', false);
            }
        });

        var srcs=[];
        $('#course-details').find('img').each(function() { 
            var src = $(this).attr('src');
            if(!(jQuery.inArray(src, srcs) != -1)) {
                srcs.push(src);
                if (src && !src.match(/^http([s]?):\/\/.*/) && !src.match(/^data([s]?):.*/)) {
                    $(this).attr('src',document.location.origin+'/public/storage/uploads/'+src);
                }
            }
        });
    });
    function initAudio(action) {
        const audio = new Audio($('#audioPlayer').attr('src'));
        if(action) {
            audio.play();
        } else {
            audio.stop();
        }
    }
    </script>
    @if(!empty($lesson) && get_class($lesson)=="App\Models\Test")
    <script src='https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.1/modernizr.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js'></script>
    <script src="{{ asset('js/formAnimation.js') }}"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script src="{{ asset('js/shake.js') }}"></script>
    @endif
@endpush