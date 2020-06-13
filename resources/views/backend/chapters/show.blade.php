@extends('backend.layouts.app')
@section('title', __('Cahpter').' | '.app_name())

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">Chapter</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>ID</th>
                            <td>{{ $chapter->id }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.title')</th>
                            <td>{{ $chapter->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.slug')</th>
                            <td>{{ $chapter->slug }}</td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td>{{ $course->title }}</td>
                        </tr>
                        <tr>
                            <th>Assigned Lessons</th>
                            <td>
                                @foreach($lessons as $key => $lesson)
                                <p>{{$key+1}}). {{$lesson->title}}</p>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.published')</th>
                            <td>{{ Form::checkbox("published", 1, $chapter->published == 1 ? true : false, ["disabled"]) }}</td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->



            <a href="{{ route('admin.chapters.index') }}"
               class="btn btn-default border">@lang('strings.backend.general.app_back_to_list')</a>
        </div>
    </div>
@stop