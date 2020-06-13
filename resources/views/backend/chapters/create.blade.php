@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@push('after-styles')
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

    </style>

@endpush

@section('content')
<link rel="stylesheet" href="{{url('public/js/jquery.multiselect.css')}}">
    {!! Form::open(['method' => 'POST', 'route' => ['admin.chapters.store'], 'files' => true,]) !!}
    {!! Form::hidden('model_id',0,['id'=>'lesson_id']) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">Create Chapter</h3>
            <div class="float-right">
                <a href="{{ route('admin.chapters.index') }}"
                   class="btn btn-success">View Chapters</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('title', trans('labels.backend.lessons.fields.title').'*', ['class' => 'control-label']) !!}
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.fields.title'), 'required' => '']) !!}
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('slug',trans('labels.backend.lessons.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', old('slug'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.slug_placeholder')]) !!}

                </div>
            </div> -->
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('course_id', trans('labels.backend.lessons.fields.course'), ['class' => 'control-label']) !!}
                    {!! Form::select('course_id', $courses,  (request('course_id')) ? request('course_id') : old('course_id'), ['class' => 'form-control select2','onchange'=>'getlessons(this.value)']) !!}
                </div>
            </div>
            <div class="row" id="lessons_div">
                <div class="col-12 form-group">
                    {!! Form::label('lessons', 'Select Lesson(s)', ['class' => 'control-label']) !!}
                    <select name="lessons[]" class="form-control" multiple id="lessons">
                    </select>
                </div>
                <script>
                    // function selctoer() {
                        // $('#lessons').multiselect({
                        //     columns: 1,
                        //     placeholder: 'Select Lesson(s)',
                        //     search: true,
                        //     selectAll: true
                        // });
                    // }
                    function getlessons(id) {
                        $("#lessons").html('');
                        $.ajax({
                            type: 'GET',
                            url: '{{url("user/chapters/lessons")}}/'+id,
                            success: function (data) {
                                $("#lessons").html(data);
                            },
                            error: function() { 
                                 console.log(data);
                            }
                        });
                    }
                </script>
            </div>

            <div class="row">

                <div class="col-12 col-lg-3 form-group">
                    <div class="checkbox">
                        {!! Form::hidden('published', 0) !!}
                        {!! Form::checkbox('published', 1, false, []) !!}
                        {!! Form::label('published', trans('labels.backend.lessons.fields.published'), ['class' => 'checkbox control-label font-weight-bold']) !!}
                    </div>
                </div>
                <div class="col-12  text-left form-group">
                    {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn  btn-danger']) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
<script src="{{url('public/js/jquery.min.js')}}"></script>
<script src="{{url('public/js/jquery.multiselect.js')}}"></script>
@push('after-scripts')
@endpush