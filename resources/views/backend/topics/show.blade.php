@extends('backend.layouts.app')
@section('title', __('labels.backend.topics.title').' | '.app_name())

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.topics.title')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.topics.fields.course')</th>
                            <td>@if($topic->course){{ $topic->course->title or '' }}@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.lesson')</th>
                            <td>@if($topic->lesson){{ $topic->lesson or '' }}@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.title')</th>
                            <td>{{ $topic->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.slug')</th>
                            <td>{{ $topic->slug }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.topic_image')</th>
                            <td>@if($topic->topic_image)<a href="{{ asset('storage/uploads/' . $topic->topic_image) }}" target="_blank"><img
                                            src="{{ asset('storage/uploads/' . $topic->topic_image) }}" height="100px"/></a>@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.short_text')</th>
                            <td>{!! $topic->short_text !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.full_text')</th>
                            <td>{!! $topic->full_text !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.position')</th>
                            <td>{{ $topic->position }}</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.topics.fields.media_pdf')</th>
                            <td>
                                @if($topic->mediaPDF != null )
                                <p class="form-group">
                                    <a href="{{$topic->mediaPDF->url}}" target="_blank">{{$topic->mediaPDF->url}}</a>
                                </p>
                                @else
                                    <p>No PDF</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.media_audio')</th>
                            <td>
                                @if($topic->mediaAudio != null )
                                <p class="form-group">
                                    <a href="{{$topic->mediaAudio->url}}" target="_blank">{{$topic->mediaAudio->url}}</a>
                                </p>
                                @else
                                    <p>No Audio</p>
                                @endif
                            </td>
                        </tr>

                        <tr>

                            <th>@lang('labels.backend.topics.fields.downloadable_files')</th>
                            <td>
                                @if(count($topic->downloadableMedia) > 0 )
                                    @foreach($topic->downloadableMedia as $media)
                                        <p class="form-group">
                                            <a href="{{ asset('storage/uploads/'.$media->name) }}"
                                               target="_blank">{{ $media->name }}
                                                ({{ $media->size }} KB)</a>
                                        </p>
                                    @endforeach
                                @else
                                    <p>No Files</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.media_video')</th>
                            <td>
                                @if($topic->mediaVideo !=  null )
                                        <p class="form-group">
                                           <a href="{{$topic->mediaVideo->url}}" target="_blank">{{$topic->mediaVideo->url}}</a>
                                        </p>
                                @else
                                    <p>No Videos</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.topics.fields.published')</th>
                            <td>{{ Form::checkbox("published", 1, $topic->published == 1 ? true : false, ["disabled"]) }}</td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->



            <a href="{{ route('admin.topics.index') }}"
               class="btn btn-default border">@lang('strings.backend.general.app_back_to_list')</a>
        </div>
    </div>
@stop