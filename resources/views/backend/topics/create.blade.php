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

    {!! Form::open(['method' => 'POST', 'route' => ['admin.topics.store'], 'files' => true,]) !!}
    {!! Form::hidden('model_id',0,['id'=>'topic_id']) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.topics.create')</h3>
            <div class="float-right">
                <a href="{{ route('admin.topics.index') }}"
                   class="btn btn-success">@lang('labels.backend.topics.view')</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('lesson_id', trans('labels.backend.topics.fields.lesson'), ['class' => 'control-label']) !!}
                    {!! Form::select('lesson_id', $lessons,  (request('lesson_id')) ? request('lesson_id') : old('lesson_id'), ['class' => 'form-control select2']) !!}
                </div>
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('title', trans('labels.backend.topics.fields.title').'*', ['class' => 'control-label']) !!}
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.topics.fields.title'), 'required' => '']) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('slug',trans('labels.backend.topics.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', old('slug'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.topics.slug_placeholder')]) !!}

                </div>
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('topic_image', trans('labels.backend.topics.fields.topic_image').' '.trans('labels.backend.topics.max_file_size'), ['class' => 'control-label']) !!}
                    {!! Form::file('topic_image', ['class' => 'form-control' , 'accept' => 'image/jpeg,image/gif,image/png']) !!}
                    {!! Form::hidden('topic_image_max_size', 8) !!}
                    {!! Form::hidden('topic_image_max_width', 4000) !!}
                    {!! Form::hidden('topic_image_max_height', 4000) !!}

                </div>
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
                    <div class="photo-block">
                        <div class="files-list"></div>
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
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('audio_files', trans('labels.backend.topics.fields.add_audio'), ['class' => 'control-label']) !!}
                    {!! Form::file('add_audio', [
                        'class' => 'form-control file-upload',
                         'id' => 'add_audio',
                        'accept' => "audio/mpeg3"

                        ]) !!}
                </div>
            </div>


            <div class="row">
                <div class="col-md-12 form-group">
                    {!! Form::label('add_video', trans('labels.backend.topics.fields.add_video'), ['class' => 'control-label']) !!}

                    {!! Form::select('media_type', ['youtube' => 'Youtube','vimeo' => 'Vimeo','upload' => 'Upload','embed' => 'Embed'],null,['class' => 'form-control', 'placeholder' => 'Select One','id'=>'media_type' ]) !!}

                    {!! Form::text('video', old('video'), ['class' => 'form-control mt-3 d-none', 'placeholder' => trans('labels.backend.topics.enter_video_url'),'id'=>'video'  ]) !!}


                    {!! Form::file('video_file', ['class' => 'form-control mt-3 d-none', 'placeholder' => trans('labels.backend.topics.enter_video_url'),'id'=>'video_file'  ]) !!}

                    @lang('labels.backend.topics.video_guide')

                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('tests', trans('labels.backend.questions.fields.tests'), ['class' => 'control-label']) !!}
                    {!! Form::select('quiz_id',  [null=>'Please Select'] + $tests, old('tests'), ['class' => 'form-control select2']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('tests'))
                        <p class="help-block">
                            {{ $errors->first('tests') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">

                <div class="col-12 col-lg-3 form-group">
                    <div class="checkbox">
                        {!! Form::hidden('published', 0) !!}
                        {!! Form::checkbox('published', 1, false, []) !!}
                        {!! Form::label('published', trans('labels.backend.topics.fields.published'), ['class' => 'checkbox control-label font-weight-bold']) !!}
                    </div>
                </div>
                <div class="col-12  text-left form-group">
                    {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn  btn-danger']) !!}
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

        $(document).on('change', '#media_type', function () {
            if ($(this).val()) {
                if ($(this).val() != 'upload') {
                    $('#video').removeClass('d-none').attr('required', true)
                    $('#video_file').addClass('d-none').attr('required', false)
                } else if ($(this).val() == 'upload') {
                    $('#video').addClass('d-none').attr('required', false)
                    $('#video_file').removeClass('d-none').attr('required', true)
                }
            } else {
                $('#video_file').addClass('d-none').attr('required', false)
                $('#video').addClass('d-none').attr('required', false)
            }
        })

    </script>

@endpush