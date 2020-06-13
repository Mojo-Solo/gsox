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
@endpush

@section('content')
    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">
                        <span>{{(isset($course->title))?$course->title:''}}</span>
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
                    @include('includes.partials.messages')
                    <div class="test-form">
                        <form action="" method="post">
                            {{ csrf_field() }}
                            <h4 class="mb-0">{{ $question->question }}</h4>
                            @if(is_file('public/storage/uploads/'.$question->question_image))
                                <img width="500" style="margin-bottom:20px;" src="{{url('public/storage/uploads/'.$question->question_image)}}" />
                                @endif
                            <br/>
                            <?php
                                $orders=array();
                            ?>
                            @foreach ($question->options as $option)
                            <?php array_push($orders, $option->id); ?>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="questions[{{ $question->id }}]"
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
                        </form>
                    </div>
                </div>
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

        const player2 = new Plyr('#audioPlayer');
        $('#audiostatus').on('change',function(){
        if($(this).is(":checked")) {
            player2.play();
        } else {
            player2.stop();
        }
      });

        $('#notice').on('hidden.bs.modal', function () {
            location.reload();
        });

        $("#sidebar").stick_in_parent();


        
        
    
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
                if (src && !src.match(/^http([s]?):\/\/.*/)) {
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
@endpush