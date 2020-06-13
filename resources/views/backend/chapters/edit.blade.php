@extends('backend.layouts.app')
@section('title', __('Chapter').' | '.app_name())

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
<script src="{{url('public/js/jquery.min.js')}}"></script>
<script src="{{url('public/js/jquery.multiselect.js')}}"></script>
    {!! Form::model($chapter, ['method' => 'PUT', 'route' => ['admin.chapters.update', $chapter->id], 'files' => true]) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">Edit Chapter</h3>
            <div class="float-right">
                <a href="{{ route('admin.chapters.index') }}"
                   class="btn btn-success">View Chapters</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('title', trans('labels.backend.lessons.fields.title').'*', ['class' => 'control-label']) !!}
                    {!! Form::text('title', $chapter->title, ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.fields.title'), 'required' => '']) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('lessons', 'Select Lesson(s)', ['class' => 'control-label']) !!}
                        <select name="lessons[]" class="form-control" multiple id="lessons">
                            @foreach($lessons as $key => $lesson)
                                <option value="{{$lesson->id}}" {{(in_array($lesson->id, $lesson_ids))?'selected':''}}>{{$lesson->title}}</option>
                            @endforeach
                        </select>
                </div>
                <script>
                    $('#lessons').multiselect({
                        columns: 1,
                        placeholder: 'Select Lesson(s)',
                        search: true,
                        selectAll: true
                    });
                </script>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('course','Course', ['class' => 'control-label']) !!}
                    {!! Form::text('', $course->title, ['class' => 'form-control', 'disabled'=>'' , 'placeholder' => 'Course']) !!}

                </div>
            </div>
            <!-- <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('slug',trans('labels.backend.lessons.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', $chapter->slug, ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.slug_placeholder')]) !!}

                </div>
            </div> -->
            <div class="row">
                <div class="col-12 col-lg-3  form-group">
                    {!! Form::hidden('published', 0) !!}
                    {!! Form::checkbox('published', 1, $chapter->published, []) !!}
                    {!! Form::label('published', trans('labels.backend.lessons.fields.published'), ['class' => 'control-label control-label font-weight-bold']) !!}
                </div>
                <div class="col-12  text-left form-group">
                    {!! Form::submit(trans('strings.backend.general.app_update'), ['class' => 'btn  btn-primary']) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop