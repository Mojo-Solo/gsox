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
                    <div class="course-details-item border-bottom-0 mb-0">
                        @if(isset($lesson->topic_image) && !empty($lesson->topic_image))
                            <div class="course-single-pic mb30">
                                <img src="{{asset('storage/uploads/'.$lesson->topic_image)}}"
                                     alt="">
                            </div>
                        @endif
                            <div class="course-single-text">
                                <div class="course-title mt10 headline relative-position">
                                    <h3>
                                        <b>@lang('labels.frontend.course.test')
                                            : {{$lesson->title}}</b>
                                    </h3>
                                </div>
                                <div class="course-details-content">
                                    <p> {!! $lesson->full_text !!} </p>
                                </div>
                            </div>
                            <hr/>
                            <hr/>

                        @if($lesson->mediaPDF)
                            <div class="course-single-text mb-5">
                                <iframe src="{{asset('storage/uploads/'.$lesson->mediaPDF->name)}}" width="100%" height="500px">
                                </iframe>
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
                        @endif
                        
                        @if(!$lesson->mediaAudio)
                        @if(is_file(public_path('/storage/uploads/'.$lesson->topic_audio)))
                        <?php 
                            $path = parse_url(public_path('/storage/uploads/'.$lesson->topic_audio), PHP_URL_PATH);
                            if(!\App\Models\Media::where('model_id',$lesson->id)->count()) {
                                $media=new \App\Models\Media();
                                $media->model_type="App\Models\Topic";
                                $media->model_id=$lesson->id;
                                $media->name=basename($path);
                                $media->url=url('/public/storage/uploads/'.$lesson->topic_audio);
                                $media->type="topic_audio";
                                $media->file_name=basename($path);
                                $media->size=\File::size($path);
                                $media->save();
                            }
                        ?>
                        <div class="card p-2 hide">
                            <h6 class="card-title">Audio</h6>
                            <div class="course-single-text mb-5">
                                <audio id="audioPlayer" {{(Session::has("audiostatus") && Session::get("audiostatus")==1)?'autoplay':''}}>
                                    <source src="{{url('/public/storage/uploads/'.$lesson->topic_audio)}}" type="audio/mp3"/>
                                </audio>
                            </div>
                        </div>
                        @endif
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

                    <!-- /market guide -->

                    <!-- /review overview -->
                </div>

                <div class="col-md-3">
                    <div id="sidebar" class="sidebar">
                        <div class="course-details-Client ul-li">
                            @if(isset($lesson->mediaAudio) && $lesson->mediaAudio)
                            <label class="label-switch switch-info">
                                <input type="checkbox" class="switch-square switch-bootstrap status" name="audiostatus" id="audiostatus" value="0">
                                <span class="lable"></span>
                                <h7>Audio</h7>
                            </label>
                            @endif
                        </div>
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


        @if($lesson->mediaVideo && $lesson->mediaVideo->type != 'embed')
        var current_progress = 0;

        @if($lesson->mediaPDF)
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
        });


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
@endpush