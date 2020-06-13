@extends('backend.layouts.app')
@section('title', __('labels.backend.topics.title').' | '.app_name())

@push('after-styles')
<link rel="stylesheet" href="{{ url('public/css/summernote-bs4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
        }

        .bootstrap-tagsinput {
            width: 100% !important;
            display: inline-block;
        }

        .bootstrap-tagsinput .tag {
            line-height: 1;
            margin-right: 2px;
            background-color: #2f353a;
            color: white;
            padding: 3px;
            border-radius: 3px;
        }
        .progress-bar.animate {
           width: 100%;
        }

    </style>

@endpush
@section('content')
<div class="section-title mb45 headline text-center">
    @if(Session::has('success'))
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h2>  {{session('success')}}</h2>
    @endif
    @if(Session::has('failure'))
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h2>  {{session('failure')}}</h2>
    @endif
</div>
    {!! Form::model($topic, ['method' => 'PUT', 'route' => ['admin.topics.update', $topic->id], 'files' => true,]) !!}
    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.topics.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.topics.preview',$topic->id) }}" target="_blank" class="btn btn-success">View Topic</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('lesson_id', trans('labels.backend.topics.fields.lesson'), ['class' => 'control-label']) !!}
                    {!! Form::select('lesson_id', $lessons, old('lesson_id'), ['class' => 'form-control select2']) !!}
                </div>
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('parent_topic', trans('labels.backend.topics.fields.parent_topic'), ['class' => 'control-label']) !!}
                    {!! Form::select('parent_topic', $parent_topics, old('parent_topic'), ['class' => 'form-control select2']) !!}
                </div>
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('title', trans('labels.backend.topics.fields.title').'*', ['class' => 'control-label']) !!}
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.topics.fields.title'), 'required' => '']) !!}

                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('slug', trans('labels.backend.topics.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', old('slug'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.topics.slug_placeholder')]) !!}
                </div>
                @if ($topic->topic_image)

                    <div class="col-12 col-lg-5 form-group">

                        {!! Form::label('topic_image', trans('labels.backend.topics.fields.topic_image').' '.trans('labels.backend.topics.max_file_size'), ['class' => 'control-label']) !!}
                        {!! Form::file('topic_image', ['class' => 'form-control', 'accept' => 'image/jpeg,image/gif,image/png', 'style' => 'margin-top: 4px;']) !!}
                        {!! Form::hidden('topic_image_max_size', 8) !!}
                        {!! Form::hidden('topic_image_max_width', 4000) !!}
                        {!! Form::hidden('topic_image_max_height', 4000) !!}
                        @if(!empty($topic->topic_image))
                        <button type="button" class="btn btn-danger mt-1" id="delete_topic_image" data-id="{{$topic->id}}"><i class="fa fa-trash"></i> Delete Image</button>
                        @endif
                    </div>
                    <div class="col-lg-1 col-12 form-group" id="topic_image_div">
                        <a href="{{ asset('storage/uploads/'.$topic->topic_image) }}" target="_blank"><img
                                    src="{{ asset('storage/uploads/'.$topic->topic_image) }}" height="65px"
                                    width="65px"></a>
                    </div>
                @else
                    <div class="col-12 col-lg-6 form-group">

                        {!! Form::label('topic_image', trans('labels.backend.topics.fields.topic_image').' '.trans('labels.backend.topics.max_file_size'), ['class' => 'control-label']) !!}
                        {!! Form::file('topic_image', ['class' => 'form-control']) !!}
                        {!! Form::hidden('topic_image_max_size', 8) !!}
                        {!! Form::hidden('topic_image_max_width', 4000) !!}
                        {!! Form::hidden('topic_image_max_height', 4000) !!}
                    </div>
                @endif

            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('full_text', trans('labels.backend.topics.fields.full_text'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('full_text', old('full_text'), ['class' => 'form-control summernote', 'placeholder' => '']) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('downloadable_files', trans('labels.backend.topics.fields.downloadable_files').' '.trans('labels.backend.topics.max_file_size'), ['class' => 'control-label']) !!}
                    {!! Form::file('downloadable_files[]', [
                        'multiple',
                        'class' => 'form-control file-upload',
                         'id' => 'downloadable_files',
                        'accept' => "image/jpeg,image/gif,image/png,application/msword,audio/mpeg,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint,application/pdf,video/mp4"

                        ]) !!}
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            @if(count($topic->downloadableMedia) > 0)
                                @foreach($topic->downloadableMedia as $media)
                                    <p class="form-group">
                                        <a href="{{ asset('storage/uploads/'.$media->name) }}"
                                           target="_blank">{{ $media->name }}
                                            ({{ $media->size }} KB)</a>
                                        <a href="#" data-media-id="{{$media->id}}"
                                           class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.topics.remove')</a>
                                    </p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('pdf_files', trans('labels.backend.topics.fields.add_pdf'), ['class' => 'control-label']) !!}
                    {!! Form::file('add_pdf', [
                        'class' => 'form-control file-upload',
                         'id' => 'add_pdf',
                        'accept' => "application/pdf"
                        ]) !!}
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            @if($topic->mediaPDF)
                                <p class="form-group">
                                    <a href="{{ asset('storage/uploads/'.$topic->mediaPDF->name) }}"
                                       target="_blank">{{ $topic->mediaPDF->name }}
                                        ({{ $topic->mediaPDF->size }} KB)</a>
                                    <a href="#" data-media-id="{{$topic->mediaPDF->id}}"
                                       class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.topics.remove')</a>
                                    <iframe src="{{asset('storage/uploads/'.$topic->mediaPDF->name)}}" width="100%" height="500px">
                                    </iframe>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('pdf_files', trans('labels.backend.topics.fields.add_audio'), ['class' => 'control-label']) !!}
                    {!! Form::file('add_audio', [
                        'class' => 'form-control file-upload',
                         'id' => 'add_audio',
                        'accept' => "audio/mpeg3"
                        ]) !!}
                    <div class="photo-block mt-3">
                        <div class="files-list">
                            <?php 
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
                            @if($topic->mediaAudio)
                                <p class="form-group">
                                    <a href="{{ asset('storage/uploads/'.$topic->mediaAudio->name) }}"
                                       target="_blank">{{ $topic->mediaAudio->name }}
                                        ({{ $topic->mediaAudio->size }} KB)</a>
                                    <a href="#" data-media-id="{{$topic->mediaAudio->id}}"
                                       class="btn btn-xs btn-danger delete remove-file">@lang('labels.backend.topics.remove')</a>
                                    <audio id="player" controls>
                                        <source src="{{ $topic->mediaAudio->url }}" type="audio/mp3" />
                                    </audio>
                                </p>
                            @elseif(check_url($topic->topic_audio)=='200')
                            <p class="form-group">
                                    <audio id="player" controls>
                                        <source src="{{ $topic->topic_audio }}" type="audio/mp3" />
                                    </audio>
                                </p>
                            @elseif(check_url(asset('storage/uploads/'.$topic->topic_audio))=='200')
                            <p class="form-group">
                                    <audio id="player" controls>
                                        <source src="{{ asset('storage/uploads/'.$topic->topic_audio) }}" type="audio/mp3" />
                                    </audio>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    {!! Form::label('add_video', trans('labels.backend.topics.fields.add_video'), ['class' => 'control-label']) !!}
                    {!! Form::select('media_type', ['youtube' => 'Youtube','vimeo' => 'Vimeo','upload' => 'Upload','embed' => 'Embed'],($topic->mediavideo) ? $topic->mediavideo->type : null,['class' => 'form-control', 'placeholder' => 'Select One','id'=>'media_type' ]) !!}


                    {!! Form::text('video', ($topic->mediavideo) ? $topic->mediavideo->url : null, ['class' => 'form-control mt-3 d-none', 'placeholder' => trans('labels.backend.topics.enter_video_url'),'id'=>'video'  ]) !!}

                    {!! Form::file('video_file', ['class' => 'form-control mt-3 d-none', 'placeholder' => trans('labels.backend.topics.enter_video_url'),'id'=>'video_file','accept' =>'video/mp4'  ]) !!}
                    <input type="hidden" name="old_video_file"
                           value="{{($topic->mediavideo && $topic->mediavideo->type == 'upload') ? $topic->mediavideo->url  : ""}}">


                    @if($topic->mediavideo && ($topic->mediavideo->type == 'upload'))
                        <video width="300" class="mt-2 d-none video-player" controls>
                            <source src="{{($topic->mediavideo && $topic->mediavideo->type == 'upload') ? $topic->mediavideo->url  : ""}}"
                                    type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>

                    @endif

                    @lang('labels.backend.topics.video_guide')
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('quiz_id', trans('labels.backend.questions.fields.tests'), ['class' => 'control-label']) !!}
                    {!! Form::select('quiz_id', $tests, old('tests'), ['class' => 'form-control select2']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('tests'))
                        <p class="help-block">
                            {{ $errors->first('tests') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-3  form-group">
                    {!! Form::hidden('published', 0) !!}
                    {!! Form::checkbox('published', 1, old('published'), []) !!}
                    {!! Form::label('published', trans('labels.backend.topics.fields.published'), ['class' => 'control-label control-label font-weight-bold']) !!}
                </div>
                <div class="col-12  text-left form-group">
                    {!! Form::submit(trans('strings.backend.general.app_update'), ['class' => 'btn  btn-primary']) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    <div class="modal js-loading-bar">
     <div class="modal-dialog">
       <div class="modal-content">
         <div class="modal-body">
           <div class="progrssval text-center">0%</div>
           <div class="progress progress-popup">
            <div class="progress-bar"></div>
           </div>
         </div>
       </div>
     </div>
    </div>
@stop

@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js')}}"></script>
    <script type="text/javascript" src="{{ url('public/js/summernote-bs4.js') }}"></script>
    <script type="text/javascript" src="{{ url('public/js/filenote.js') }}"></script>
    <script>
        $(".summernote").summernote({
            placeholder: 'Enter description here...',
            height: 300,
            callbacks: {
                onImageUpload : function(files, editor, welEditable) {
                     for(var i = files.length - 1; i >= 0; i--) {
                             sendFile(files[i], this);
                    }
                },
                onFileUpload: function(file) {
                    myOwnCallBack(file[0]);
                },
            },
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video','audio','file']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
        });
        function sendFile(file, el) {
            var token="{{csrf_token()}}";
            var form_data = new FormData();
            form_data.append('_token', token);
            form_data.append('image', file);
            $.ajax({
                data: form_data,
                type: "POST",
                url: "{{route('admin.upload.image')}}",
                cache: false,
                contentType: false,
                processData: false,
                success: function(url) {
                    if(url) {
                        $(el).summernote('editor.insertImage', url);
                    }
                },error:function(err) {
                    console.log(err);
                }
            });
        }
        function myOwnCallBack(file) {
            var token="{{csrf_token()}}";
            let data = new FormData();
            data.append('_token', token);
            data.append("file", file);
            $.ajax({
                data: data,
                type: "POST",
                url: "{{route('admin.upload.file')}}",
                cache: false,
                contentType: false,
                dataType: 'json',
                processData: false,
                xhr: function() {
                    let myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) myXhr.upload.addEventListener('progress', progressHandlingFunction, false);
                    return myXhr;
                },
                success: function(reponse) {
                        let listMimeImg = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg'];
                        let listMimeAudio = ['audio/mpeg', 'audio/ogg'];
                        let listMimeVideo = ['video/mpeg', 'video/mp4', 'video/webm'];
                        let elem;

                        if (listMimeImg.indexOf(file.type) > -1) {
                            $('.summernote').summernote('editor.insertImage', reponse['url']);
                        } else if (listMimeAudio.indexOf(file.type) > -1) {
                            elem = document.createElement("audio");
                            elem.setAttribute("src", reponse['url']);
                            elem.setAttribute("controls", "controls");
                            elem.setAttribute("preload", "metadata");
                            $('.summernote').summernote('editor.insertNode', elem);
                        } else if (listMimeVideo.indexOf(file.type) > -1) {
                            elem = document.createElement("video");
                            elem.setAttribute("src", reponse['url']);
                            elem.setAttribute("controls", "controls");
                            elem.setAttribute("preload", "metadata");
                            $('.summernote').summernote('editor.insertNode', elem);
                        } else {
                            elem = document.createElement("a");
                            let linkText = document.createTextNode(file.name);
                            elem.appendChild(linkText);
                            elem.title = file.name;
                            elem.href = reponse['url'];
                            $('.summernote').summernote('editor.insertNode', elem);
                        }
                }
            });
        }

        function progressHandlingFunction(e) {
            if (e.lengthComputable) {
                var width=parseFloat(e.loaded / e.total * 100).toFixed(2);
                var $modal = $('.js-loading-bar'),
                $bar = $modal.find('.progress-bar');
                $modal.modal('show');
                $modal.find('.progrssval').html(width+'%');
                $bar.css("width", width+'%');
                if (e.loaded === e.total) {
                    $modal.find('.progrssval').html('0%');
                    $bar.css("width", '0%');
                    $modal.modal('hide');
                }
            }
        }
        this.$('.js-loading-bar').modal({
          backdrop: 'static',
          show: false
        });
        $(document).ready(function () {
        $('#delete_topic_image').on('click',function(){
           $(this).html('<i class="fa fa-spin fa-spinner"></i> Processing');
           $(this).attr('disabled',true);
            $.ajax({
	          type: "DELETE",
	          url: "{{route('admin.topics.image_del',$topic->id)}}",
	          data: {_token: '{{csrf_token()}}'},
	          dataType: 'json',
	          success: function(data){
	        	console.log(data);
	        	$('#delete_topic_image').html('<i class="fa fa-trsh"></i> Delete Image');
                $('#delete_topic_image').attr('disabled',false);
                $('#topic_image_div').remove();
                $('#delete_topic_image').remove();
	          }, error: function (error) {
	            $('#delete_topic_image').html('<i class="fa fa-trsh"></i> Delete Image');
                $('#delete_topic_image').attr('disabled',false);
	            console.log(error);
	            alert('Something Went Wrong');
	          }
			});
        });
            $(document).on('click', '.delete', function (e) {
                e.preventDefault();
                var parent = $(this).parent('.form-group');
                var confirmation = confirm('{{trans('strings.backend.general.are_you_sure')}}')
                if (confirmation) {
                    var media_id = $(this).data('media-id');
                    $.post('{{route('admin.media.destroy')}}', {media_id: media_id, _token: '{{csrf_token()}}'},
                        function (data, status) {
                            if (data.success) {
                                parent.remove();
                            } else {
                                alert('Something Went Wrong');
                            }
                        });
                }
            })
        });

        var uploadField = $('input[type="file"]');


        $(document).on('change', 'input[name="topic_image"]', function () {
            var $this = $(this);
            $(this.files).each(function (key, value) {
                if (value.size > 5000000) {
                    alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
                    $this.val("");
                }
            })
        });

        @if($topic->mediavideo)
        @if($topic->mediavideo->type !=  'upload')
        $('#video').removeClass('d-none').attr('required', true);
        $('#video_file').addClass('d-none').attr('required', false);
        $('.video-player').addClass('d-none');
        @elseif($topic->mediavideo->type == 'upload')
        $('#video').addClass('d-none').attr('required', false);
        $('#video_file').removeClass('d-none').attr('required', false);
        $('.video-player').removeClass('d-none');
        @else
        $('.video-player').addClass('d-none');
        $('#video_file').addClass('d-none').attr('required', false);
        $('#video').addClass('d-none').attr('required', false);
        @endif
        @endif

        $(document).on('change', '#media_type', function () {
            if ($(this).val()) {
                if ($(this).val() != 'upload') {
                    $('#video').removeClass('d-none').attr('required', true);
                    $('#video_file').addClass('d-none').attr('required', false);
                    $('.video-player').addClass('d-none')
                } else if ($(this).val() == 'upload') {
                    $('#video').addClass('d-none').attr('required', false);
                    $('#video_file').removeClass('d-none').attr('required', true);
                    $('.video-player').removeClass('d-none')
                }
            } else {
                $('#video_file').addClass('d-none').attr('required', false);
                $('#video').addClass('d-none').attr('required', false)
            }
        })

    </script>
@endpush