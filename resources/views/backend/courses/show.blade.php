@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/amigo-sorter/css/theme-default.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/noty.css')}}">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link href="{{asset('css/toggle.css')}}" rel="stylesheet">
    <style>
        ul.sorter > span {
            display: inline-block;
            width: 100%;
            height: 100%;
            background: #f5f5f5;
            color: #333333;
            border: 1px solid #cccccc;
            border-radius: 6px;
            padding: 0px;
        }
        .hide{
            display:none !important;
        }
        div.lesson {
            display: flex;
        }
        .right{
            float: right;
        }
        .btn-info,.btn-delete,.btn-add {
            height:37px;
            width:3% !important;
        }
        .search-results,.modal-search-results{
            background: #fff;
            border-left: 1px solid darkgrey;
            border-right: 1px solid darkgrey;
            border-bottom: 1px solid darkgrey;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
            max-height: 250px;
            overflow: auto;
            position: absolute;
            width: 95.3%;
        }
        .modal-search-results {
            width:93.5%;
            max-height: 280px;
        }
        .list-style-none{
            list-style: none;
            display: contents;
        }
        .result-item {
            border-top: 1px solid lightgrey;
            background: #eee;
            padding: 2px 10px;
        }
        .btn-edit {
            /*float: right;*/
        }
        .has-search .form-control {
            padding-left: 2.375rem;
        }

        .has-search .form-control-feedback {
            position: absolute;
            z-index: 2;
            display: block;
            width: 2.375rem;
            height: 2.375rem;
            line-height: 2.375rem;
            text-align: center;
            pointer-events: none;
            color: #aaa;
        }
        ul.sorter li > span .title {
            padding-left: 15px;
            width: 70%;
        }

        ul.sorter li > span .btn {
            width: 10%;
        }

        @media screen and (max-width: 768px) {

            ul.sorter li > span .btn {
                width: 30%;
            }

            ul.sorter li > span .title {
                padding-left: 15px;
                width: 70%;
                float: left;
                margin: 0 !important;
            }

        }


    </style>
@endpush

@section('content')

    <div class="card">

        <div class="card-header">
            <h3 class="page-title mb-0">@lang('labels.backend.courses.title')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.courses.fields.Supervisors')</th>
                            <td>
                                @foreach ($course->Supervisors as $singleSupervisors)
                                    <span class="label label-info label-many">{{ $singleSupervisors->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.title')</th>
                            <td>
                                @if($course->published == 1)
                                    <a target="_blank"
                                       href="{{ route('courses.show', [$course->slug]) }}">{{ $course->title }}</a>
                                @else
                                    {{ $course->title }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.slug')</th>
                            <td>{{ $course->slug }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.Client')</th>
                            <td>{{ $course->Client->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.description')</th>
                            <td>{!! $course->description !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.price')</th>
                            <td>{{ ($course->free == 1) ? trans('labels.backend.courses.fields.free') : $course->price.' '.$appCurrency['symbol'] }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.course_header_image')</th>
                            <td>@if($course->course_header_image)<a
                                        href="{{ asset('storage/uploads/' . $course->course_header_image) }}"
                                        target="_blank"><img
                                            src="{{ asset('storage/uploads/' . $course->course_header_image) }}"
                                            height="50px"/></a>@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.course_image')</th>
                            <td>@if($course->course_image)<a
                                        href="{{ asset('storage/uploads/' . $course->course_image) }}"
                                        target="_blank"><img
                                            src="{{ asset('storage/uploads/' . $course->course_image) }}"
                                            height="50px"/></a>@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.media_video')</th>
                            <td>
                                @if($course->mediaVideo !=  null )
                                    <p class="form-group mb-0">
                                        <a href="{{$course->mediaVideo->url}}" target="_blank">{{$course->mediaVideo->url}}</a>
                                    </p>
                                @else
                                    <p>No Videos</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.start_date')</th>
                            <td>{{ $course->start_date }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.published')</th>
                            <td>{{ Form::checkbox("published", 1, $course->published == 1 ? true : false, ["disabled"]) }}</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.courses.fields.meta_title')</th>
                            <td>{{ $course->meta_title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.meta_description')</th>
                            <td>{{ $course->meta_description }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.courses.fields.meta_keywords')</th>
                            <td>{{ $course->meta_keywords }}</td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->

            @if(count($courseTimeline) > 0)
                <div class="row justify-content-center">
                    <div class="col-12  ">
                        <div class="row">
                            <div class="col-4"><p class="offset-2 col-sm-8 d-inline-block mb-0 btn btn-warning text-white">@lang('labels.backend.faqs.fields.question')</p></div>
                            <div class="col-4"><p class="offset-2 col-sm-8 d-inline-block mb-0 btn btn-success">Lesson</p></div>
                            <div class="col-4"><p class="offset-2 col-sm-8 d-inline-block mb-0 btn btn-primary">Test</p></div>
                            @if(!auth()->user()->hasRole('supervisor'))
                            <div class="form-group has-search col-6 offset-md-3 mt-4" id="searchbox-div">
                                <span class="fa fa-search form-control-feedback"></span>
                                <input type="text" class="form-control" autocomplete="off" id="searchbox" placeholder="Search Questions">
                                <div class="search-results" style="display: none;">
                                    <ul class="list-style-none">
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if(!auth()->user()->hasRole('supervisor'))
                        <label class="label-switch switch-primary">
                    	<input type="checkbox" class="switch switch-bootstrap status" name="showhidelessons" id="showhidelessons" value="{{$course->showhidelessons}}" {{($course->showhidelessons)?'checked':''}}>
                    	<span class="lable"> Show/Hide Lessons</span></label>
                        @endif
                        <h4 class="">@lang('labels.backend.courses.course_timeline')</h4>
                        <p class="mb-0">@lang('labels.backend.courses.listing_note')</p>
                        <p class="">@lang('labels.backend.courses.timeline_description')</p>
                        @if(!auth()->user()->hasRole('supervisor'))
                        <a href="javascript:void(0);" id="save_timeline1" class="btn btn-primary float-right mb-2 mt-0">@lang('labels.backend.courses.save_timeline')</a>
                        @endif
                        <ul class="sorter d-inline-block">
                            @php
                            $chapters=\App\Models\Chapter::where('course_id',$course->id)->get();
                            @endphp
                            @foreach($courseTimeline as $key=>$item)
                                @if($item->model_type == 'App\Models\Question' || isset($item->model->published) && $item->model->published == 1)
                                <!-- <div class="lesson"> -->
                                @php
                                $chapter_title='';
                                foreach($chapters as $key => $chapter) {
                                    $lessons=explode(",", $chapter->lesson_ids);
                                    if(in_array($item->model->id, $lessons)) {
                                        $chapter_title=$chapter->title;
                                    }
                                }
                                @endphp
                                <li id="li{{$item->id}}" class="{{($item->model_type == 'App\Models\Lesson')?'lessons_li':''}} {{($item->model_type == 'App\Models\Lesson' && $course->showhidelessons)?'hide':''}}">
                                    <span data-id="{{$item->id}}" data-sequence="{{$item->sequence}}">
                                        @if(!empty($chapter_title))
                                        <p class="d-inline-block mb-0 btn btn-warning">
                                            <span class="text-white">{{$chapter_title}}</span>
                                        </p>
                                        @endif
                                      @if($item->model_type == 'App\Models\Test')
                                            <p class="d-inline-block mb-0 btn btn-primary">
                                                @lang('labels.backend.courses.test')
                                            </p>
                                        @elseif($item->model_type == 'App\Models\Lesson')
                                            <p class="d-inline-block mb-0 btn btn-success">
                                                @lang('labels.backend.courses.lesson')
                                            </p>
                                        @elseif($item->model_type == 'App\Models\Question')
                                            <p class="d-inline-block mb-0 btn btn-warning">
                                                @lang('labels.backend.faqs.fields.question')
                                            </p>
                                        @endif
                                        @if($item->model)
                                        <p class="title d-inline ml-2">{{($item->model_type == 'App\Models\Question')?$item->model->question:$item->model->title}}</p>
                                        @endif
                                        @if(!auth()->user()->hasRole('supervisor'))
                                            @if($item->model_type == 'App\Models\Lesson')
                                            <a class="btn btn-danger btn-delete right" href="{{url('user/models/'.$item->id.'/trash')}}" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a>
                                            <a target="_blank" class="btn btn-info btn-edit right mr-1" href="{{url('user/lessons/'.$item->model->id.'/edit')}}"><i class="fa fa-pencil"></i></a>
                                            @elseif($item->model_type == 'App\Models\Topic')
                                            <a class="btn btn-danger btn-delete right" href="{{url('user/models/'.$item->id.'/trash')}}" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a>
                                            <a target="_blank" class="btn btn-info btn-edit right mr-1" href="{{url('user/topics/'.$item->model->id.'/edit')}}"><i class="fa fa-pencil"></i></a>
                                            @elseif($item->model_type == 'App\Models\Test')
                                            <a class="btn btn-danger btn-delete right" href="{{url('user/models/'.$item->id.'/trash')}}" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a>
                                            <a target="_blank" class="btn btn-info btn-edit right mr-1" href="{{url('user/tests/'.$item->model->id.'/edit')}}"><i class="fa fa-pencil"></i></a>
                                            @elseif($item->model_type == 'App\Models\Question')
                                            <a class="btn btn-danger btn-delete right" href="{{url('user/models/'.$item->id.'/trash')}}" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a>
                                            @if(isset($item->model))
                                            <a target="_blank" class="btn btn-info btn-edit right mr-1" href="{{url('user/questions/'.$item->model->id.'/edit')}}?course_id={{$course->id}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @endif
                                            <button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,{{$item->id}})" data-target="#addquestions"><i class="fa fa-plus"></i></button>
                                        @endif
                                   </span>
                                </li>
                                <!-- </div> -->
                                @endif
                            @endforeach
                        </ul>
                        <a href="{{ route('admin.courses.index') }}"
                           class="btn btn-default border float-left">@lang('strings.backend.general.app_back_to_list')</a>
                        @if(!auth()->user()->hasRole('supervisor'))
                        <a href="javascript:void(0);" id="save_timeline"
                           class="btn btn-primary float-right">@lang('labels.backend.courses.save_timeline')</a>
                        @endif
                    </div>

                </div>
            @endif
        </div>
    </div>
@if(!auth()->user()->hasRole('supervisor'))
<div class="modal fade" id="testModal" tabindex="-1" role="dialog" aria-labelledby="testModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Course Tests</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('admin.courses.saveQuestion')}}" method="post" autocomplete="off">
        {{csrf_field()}}
        <input type="hidden" name="qid" id="qid" />
        <input type="hidden" name="cid" id="cid" value="{{$course->id}}" />
          <div class="modal-body">
            <div class="form-group">
                <label for="course-tests">Select Test</label>
                <select class="form-control" id="course-tests" name="coursetest">
                    @foreach($course->tests as $key => $test)
                        <option value="{{$test->id}}">{{$test->title}}</option>
                    @endforeach
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="addquestion(this)" class="btn btn-primary">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Course Tests</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form>
        {{csrf_field()}}
        <input type="hidden" name="qid" id="qid" />
        <input type="hidden" name="item_id" id="item_id" />
        <input type="hidden" name="cid" id="cid" value="{{$course->id}}" />
          <div class="modal-body">
            <div class="form-group">
                <label for="course-tests"><input type="radio" name="type" value="test"> Add in Test</label>
            </div>
            <div class="form-group">
                <label for="course-tests"><input type="radio" name="type" value="timeline"> Add in Timeline</label>
            </div>
            <div class="form-group hide" id="select_test_modal_div">
                <label for="course-tests">Select Test</label>
                <select class="form-control" id="course-tests" name="coursetest">
                    @foreach($course->tests as $key => $test)
                        <option value="{{$test->id}}">{{$test->title}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group hide" id="review_topics_modal_div">
                {!! Form::label('topic_id', 'Select Reivew Topic', ['class' => 'control-label']) !!}
                    {!! Form::select('topic_id', $topics,  (request('topic_id')) ? request('topic_id') : old('topic_id'), ['class' => 'form-control select2']) !!}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="savedataForm(this,event)" class="btn btn-primary">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Course Lessons</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form>
        {{csrf_field()}}
        <input type="hidden" name="iid" id="iid" />
        <input type="hidden" name="item_id" id="item_id" />
        <input type="hidden" name="type" id="type" />
        <input type="hidden" name="cid" id="cid" value="{{$course->id}}" />
          <div class="modal-body">
            <div class="form-group">
                <label for="course-tests">Select Lesson</label>
                <select class="form-control" id="course-lessons" name="coursetest">
                    @foreach($course->lessons as $key => $lesson)
                        <option value="{{$lesson->id}}">{{$lesson->title}}</option>
                    @endforeach
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="saveitemdata(this,event)" class="btn btn-primary">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>
<!-- add questions -->
<div class="modal fade" id="addquestions" tabindex="-1" role="dialog" aria-labelledby="addquestionsTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addquestionsTitle">Add Question/Topic/Lesson/Test</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="adddataForm">
          {{csrf_field()}}
          <input type="hidden" name="item_id" id="item_id" value="" />
          <div class="modal-body">
            <div class="form-group" id="questions_div">
                <select class="form-control" name="modeltype" required>
                    <option value="question">Question</option>
                    <option value="lesson">Lesson</option>
                    <option value="topic">Topic</option>
                    <option value="test">Test</option>
                </select>
            </div>
            <div class="form-group has-search mt-4" id="modal-searchbox-div">
                <span class="fa fa-search form-control-feedback"></span>
                <input type="text" class="form-control" autocomplete="off" name="keyword" id="modal-searchbox" placeholder="Search">
                <div class="modal-search-results" style="display: none;">
                    <ul class="list-style-none">
                    </ul>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <!-- <button type="button" class="btn btn-primary">Add</button> -->
          </div>
      </form>
    </div>
  </div>
</div>
@endif
@stop

@push('after-scripts')
    <!-- <script src="{{asset('plugins/amigo-sorter/js/amigosorter.js')}}"></script> -->
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script src="{{asset('js/noty.js')}}"></script>
    @if(!auth()->user()->hasRole('supervisor'))
    <script>
        var ul = null;
        var li_index = null;
        //Start Amigo code
        $.fn.amigoSorter = function(options) {

    var settings = $.extend({
        li_helper: "li_helper",
        li_empty: "empty",
        onTouchStart : function() {},
        onTouchMove : function() {},
        onTouchEnd : function() {}
    }, options );

    var action = false;
    var shift_left = 0;
    var shift_top = 0;
    var mouse_up_events = 'mouseup touchend';
    var mouse_move_events = 'mousemove touchmove';
    var mouse_down_events = 'mousedown touchstart';

    $('#dataModal > div > div > form > div.modal-body  input[type=radio]').on('change',function(){
        if($(this).val()=="test") {
            $('#dataModal form #select_test_modal_div').removeClass('hide');
            $('#dataModal form #review_topics_modal_div').addClass('hide');
        } else {
            $('#dataModal form #select_test_modal_div').addClass('hide');
            $('#dataModal form #review_topics_modal_div').removeClass('hide');
        }
    });
    $(document.body).append( $.fn.amigoSorter.li_helper( settings.li_helper ) ); 

    $(document).on(mouse_up_events, function(e) {
        e.stopPropagation();
        e.preventDefault();
        action = false;
        ul.attr('data-action', false);
        ul.find('li').removeClass(settings.li_empty);
        $('.' + settings.li_helper).css('display','none').html('');
        settings.onTouchEnd.call();
    });

    return this.each(function(e) {
        ul = $(this);
        $(document).on(mouse_move_events, function(e) {
            settings.onTouchMove.call();
            if (action == true) {
                if (e.type == "touchmove") {
                    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                    $.fn.amigoSorter.set_drag_pos( settings.li_helper, touch.clientX - shift_left, touch.clientY - shift_top);  
                } else {
                    $.fn.amigoSorter.set_drag_pos( settings.li_helper, e.pageX - shift_left, e.pageY - shift_top);  
                }


                ul.find('> li').each( function() {
                    var $li = $(this);
                    var $span = $li.find('> span');

                    if (!$li.hasClass(settings.li_empty)) {
                        var $li_offset = $li.offset();
                        var $span_offset = $span.offset();
                        var start_left = $span_offset.left;
                        var start_top = $span_offset.top;
                        var end_left = $span_offset.left + $span.outerWidth();
                        var end_top = $span_offset.top + $span.outerHeight();

                        var e_page_X = 0, e_page_Y = 0;

                        if (e.type == "touchmove") {
                            var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                            e_page_X = touch.clientX;
                            e_page_Y = touch.clientY;
                        } else {
                            e_page_X = e.pageX;
                            e_page_Y = e.pageY;
                        }


                        if ( e_page_X > start_left && e_page_X < end_left && e_page_Y > start_top && e_page_Y < end_top ) {
                            var hover_index = $li.index();
                            var shift_count = Math.abs(hover_index - li_index);
                            for (i = 1; i<=shift_count; i++) {
                                if (hover_index >= li_index) { 
                                    ul.find('> li').eq(li_index).insertAfter(ul.find('> li').eq(li_index + 1));
                                    li_index++;
                                }
                                else { 
                                    ul.find('> li').eq(li_index - 1).insertAfter(ul.find('> li').eq(li_index)); 
                                    li_index--;
                                }
                            }

                        }
                    }
                });

            }

        });
        ul.find('> li').on(mouse_down_events, function(e) {
            var $li = $(this);
            if ($(e.target).closest(".btn-edit").length) {return;}
            if ($(e.target).closest(".btn-delete").length) {return;}
            if ($(e.target).closest(".btn-add").length) {return;}
            ul = $li.closest('ul');
            e.stopPropagation();
            e.preventDefault();
            settings.onTouchStart.call();
            action = true;
            ul.attr('data-action', true);

            var li_offset = $li.offset();
            if (e.type == "touchstart") {
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                shift_left = touch.clientX - li_offset.left;
                shift_top = touch.clientY - li_offset.top;
            } else {
                shift_left = e.pageX - li_offset.left;
                shift_top = e.pageY - li_offset.top;
            }

            var li_html = $li.html();
            li_index = $li.index();
            $li.addClass(settings.li_empty);

            $.fn.amigoSorter.set_li_helper_size( $li, settings.li_helper);  

            if (e.type == "touchstart") {
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                $.fn.amigoSorter.set_drag_pos( settings.li_helper, touch.clientX - shift_left, touch.clientY - shift_top);  
            } else $.fn.amigoSorter.set_drag_pos( settings.li_helper, e.pageX - shift_left, e.pageY - shift_top);   

            $('.' + settings.li_helper).html(li_html).css('display','inline-block');

        });

        
    });
};

$.fn.amigoSorter.li_helper = function( helper_class ) {
    return '<span class="' + helper_class + '"></span>';
};

$.fn.amigoSorter.set_drag_pos = function( helper_class, x, y ) {
    $('.' + helper_class).css('left', x ).css('top', y );   
    return true;
};

$.fn.amigoSorter.set_li_helper_size = function( $li, helper_class ) {
    var width = $li.outerWidth();
    var height = $li.outerHeight();
    $('.' + helper_class).css('width', width + 'px').css('height', height + 'px');
    return true;
};
        //End Amigo code

        function editclick(elem) {
            alert('clicked');
        }function editmouseover(elem) {
            console.log('hovered');
        }
        function testModal(elem,ev,id) {
            console.log(item_id);
            $('#testModal form > #qid').val(id);
        }
        function dataModal(elem,ev,id,item_id,type) {
            ev.preventDefault();
            $('#addquestions').modal('toggle');
            if(type=="question") {
                $('#dataModal form > #qid').val(id);
                $('#dataModal form > #item_id').val(item_id);
            }
        }
        function itemModal(elem,ev,id,item_id,type) {
            ev.preventDefault();
            $('#addquestions').modal('toggle');
            $('#itemModal form > #iid').val(id);
            $('#itemModal form > #item_id').val(item_id);
            $('#itemModal form > #type').val(type);
        }
        function insertitem(elem,ev,id,item_id,type) {
            ev.preventDefault();
            elem.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
            // $('#addquestions').modal('toggle');
            $.ajax({
                method: 'POST',
                url: "{{route('admin.courses.savecontent')}}",
                dataType:'json',
                data: {
                    _token: '{{csrf_token()}}',
                    itemid: id,
                    type: type,
                    cid: <?php echo $course->id; ?>
                },success: function(data){
                    var contentli='<li id="li'+data['timeline']+'"><span data-id="'+data['timeline']+'" data-sequence="'+data['sequence']+'"><p class="d-inline-block mb-0 btn btn-success">Lesson</p><p class="title d-inline ml-2">'+data['question']+'</p><a class="btn btn-danger btn-delete right" href="'+document.location.origin+'/user/models/'+data['timeline']+'/trash" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a><a target="_blank" class="btn btn-info btn-edit right" href="'+document.location.origin+'/user/lessons/'+data['qid']+'/edit"><i class="fa fa-pencil"></i></a><button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,'+data['timeline']+')" data-target="#addquestions"><i class="fa fa-plus"></i></button></span></li>';
                    $('.sorter.d-inline-block > #li'+item_id).after(contentli);
                    $('ul.sorter').amigoSorter({
                        li_helper: "li_helper",
                        li_empty: "empty",
                    });
                    toastr.success("Successfully Added.",'Success');
                    $('#addquestions').modal('toggle');
                    elem.innerHTML='Save';
                },error:function(error){
                    toastr.error("Oops!, System encountered an problem.",'Error');
                    console.log(error);
                }
            });
        }
        function addquestion(elem) {
            elem.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
            $.ajax({
                method: 'POST',
                url: "{{route('admin.courses.saveQuestion')}}",
                dataType:'json',
                data: {
                    _token: '{{csrf_token()}}',
                    qid: $('#testModal form > #qid').val(),
                    tid: $('#testModal form > .modal-body #course-tests').val(),
                    type: 'test',
                    cid: <?php echo $course->id; ?>
                },success: function(data){
                    if(data['error']) {
                        toastr.error(data['error'],'Error');
                        elem.innerHTML='Save';
                    }if(data['success']) {
                        toastr.success(data['success'],'Success');
                        $('#testModal').modal('toggle');
                        elem.innerHTML='Save';
                    }
                },error:function(error){
                    toastr.error("Oops!, System encountered an problem.",'Error');
                    console.log(error);
                }
            });
        }
        
        function savedataForm(elem,e) {
            e.preventDefault();
            elem.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
            var item_id=$('#dataModal form > #item_id').val();
            $.ajax({
                method: 'POST',
                url: "{{route('admin.courses.saveQuestion')}}",
                dataType:'json',
                data: {
                    _token: '{{csrf_token()}}',
                    qid: $('#dataModal form > #qid').val(),
                    tid: $('#dataModal form > .modal-body #course-tests').val(),
                    type: $('#dataModal form > .modal-body input[type="radio"]:checked').val(),
                    topic_id: $('#dataModal form > .modal-body #topic_id').val(),
                    cid: <?php echo $course->id; ?>
                },success: function(data){
                    if(data['error']) {
                        toastr.error(data['error'],'Error');
                        elem.innerHTML='Save';
                    }if(data['success']) {
                        toastr.success(data['success'],'Success');
                        $('#dataModal').modal('toggle');
                        elem.innerHTML='Save';
                    } if(data['timeline']) {
                        var contentli='<li id="li'+data['timeline']+'"><span data-id="'+data['timeline']+'" data-sequence="'+data['sequence']+'"><p class="d-inline-block mb-0 btn btn-warning">@lang("labels.backend.faqs.fields.question")</p><p class="title d-inline ml-2">'+data['question']+'</p><a class="btn btn-danger btn-delete right" href="'+document.location.origin+'/user/models/'+data['timeline']+'/trash" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a><a target="_blank" class="btn btn-info btn-edit right" href="'+document.location.origin+'/user/questions/'+data['qid']+'/edit?course_id={{$course->id}}"><i class="fa fa-pencil"></i></a><button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,'+data['timeline']+')" data-target="#addquestions"><i class="fa fa-plus"></i></button></span></li>';
                        // ul.append(contentli);
                        $('.sorter.d-inline-block > #li'+item_id).after(contentli);
                        $('ul.sorter').amigoSorter({
                            li_helper: "li_helper",
                            li_empty: "empty",
                        });
                        // $('.sorter.d-inline-block').append(contentli);
                        // $("html, body").animate({ scrollTop: $(document).height()-$(window).height() });
                        toastr.success("Successfully Added.",'Success');
                        $('#dataModal').modal('toggle');
                        elem.innerHTML='Save';
                    }
                },error:function(error){
                    toastr.error("Oops!, System encountered an problem.",'Error');
                    console.log(error);
                }
            });
        }
        
        function saveitemdata(elem,e) {
            e.preventDefault();
            elem.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
            var item_id=$('#itemModal form > #item_id').val();
            var type=$('#itemModal form > #type').val();
            $.ajax({
                method: 'POST',
                url: "{{route('admin.courses.savecontent')}}",
                dataType:'json',
                data: {
                    _token: '{{csrf_token()}}',
                    itemid: $('#itemModal form > #iid').val(),
                    type: type,
                    lid: $('#itemModal form > .modal-body #course-lessons').val(),
                    cid: <?php echo $course->id; ?>
                },success: function(data){
                    if(type=="lesson"){
                        var contentli='<li id="li'+data['timeline']+'"><span data-id="'+data['timeline']+'" data-sequence="'+data['sequence']+'"><p class="d-inline-block mb-0 btn btn-success">Lesson</p><p class="title d-inline ml-2">'+data['question']+'</p><a class="btn btn-danger btn-delete right" href="'+document.location.origin+'/user/models/'+data['timeline']+'/trash" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a><a target="_blank" class="btn btn-info btn-edit right" href="'+document.location.origin+'/user/lessons/'+data['qid']+'/edit"><i class="fa fa-pencil"></i></a><button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,'+data['timeline']+')" data-target="#addquestions"><i class="fa fa-plus"></i></button></span></li>';
                    }
                    if(type=="test"){
                        var contentli='<li id="li'+data['timeline']+'"><span data-id="'+data['timeline']+'" data-sequence="'+data['sequence']+'"><p class="d-inline-block mb-0 btn btn-primary">Test</p><p class="title d-inline ml-2">'+data['question']+'</p><a class="btn btn-danger btn-delete right" href="'+document.location.origin+'/user/models/'+data['timeline']+'/trash" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a><a target="_blank" class="btn btn-info btn-edit right" href="'+document.location.origin+'/user/tests/'+data['qid']+'/edit"><i class="fa fa-pencil"></i></a><button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,'+data['timeline']+')" data-target="#addquestions"><i class="fa fa-plus"></i></button></span></li>';
                    }
                    if(type=="topic"){
                        var contentli='<li id="li'+data['timeline']+'"><span data-id="'+data['timeline']+'" data-sequence="'+data['sequence']+'"><p class="title d-inline ml-2">'+data['question']+'</p><a class="btn btn-danger btn-delete right" href="'+document.location.origin+'/user/models/'+data['timeline']+'/trash" onclick="tashtimeline(this,event)"><i class="fa fa-trash"></i></a><a target="_blank" class="btn btn-info btn-edit right" href="'+document.location.origin+'/user/topics/'+data['qid']+'/edit"><i class="fa fa-pencil"></i></a><button type="button" class="btn btn-primary btn-add right mr-1" data-toggle="modal" onclick="additem(this,'+data['timeline']+')" data-target="#addquestions"><i class="fa fa-plus"></i></button></span></li>';
                    }
                    $('.sorter.d-inline-block > #li'+item_id).after(contentli);
                    $('ul.sorter').amigoSorter({
                        li_helper: "li_helper",
                        li_empty: "empty",
                    });
                    toastr.success("Successfully Added.",'Success');
                    $('#itemModal').modal('toggle');
                    elem.innerHTML='Save';
                },error:function(error){
                    toastr.error("Oops!, System encountered an problem.",'Error');
                    console.log(error);
                }
            });
        }
        $(function () {
            $('ul.sorter').amigoSorter({
                li_helper: "li_helper",
                li_empty: "empty",
            });
            $('#searchbox-div > #searchbox').on('change focus keyup',function(){
                $('#searchbox-div > .search-results').show();
                $('#searchbox-div > .search-results > ul').html('<li class="result-item text-center"><i class="fa fa-spinner fa-spin"></i></li>');
                $.ajax({
                    method: 'GET',
                    url: "{{route('admin.courses.searchquestion')}}",
                    dataType: 'json',
                    data: {
                        query: $(this).val()
                    },success: function(data){
                        if(!data.length){
                            $('#searchbox-div > .search-results > ul').html('<li class="result-item text-center">No Result Found</li>');
                        } else{
                            $('#searchbox-div > .search-results > ul').html('');
                            data.forEach(function(item) {
                                if(item['question']) {
                                $('#searchbox-div > .search-results > ul').append('<li id="drag'+item['id']+'" class="result-item">'+item['question']+' <button class="btn btn-primary" onclick="testModal(this,event,'+item['id']+')" data-toggle="modal" data-target="#testModal">Add</button></li>');
                            }
                            });
                        }
                    },error:function(error){
                        console.log('error');
                        console.log(error);
                    }
                });
            });
            
            $('#addquestions #adddataForm > .modal-body > #modal-searchbox-div > #modal-searchbox').on('change focus keyup',function(e){
                e.preventDefault();
                var type=$('#addquestions #adddataForm > .modal-body > #questions_div > select').val();
                var item_id=$('#addquestions #adddataForm > #item_id').val();
                var resultsdiv=$('#addquestions #adddataForm > .modal-body > #modal-searchbox-div > .modal-search-results').show();
                resultsdiv.show();
                var ul=resultsdiv.find("ul");
                ul.html('<li class="result-item text-center"><i class="fa fa-spinner fa-spin"></i></li>');
                $.ajax({
                    method: 'POST',
                    url: '{{url("user/add/contents")}}',
                    data: $("#addquestions #adddataForm").serialize(),
                    dataType: 'json',
                    success: function(data){
                        if(!data.length){
                            $('#addquestions #adddataForm > .modal-body').css('min-height','auto');
                            ul.html('<li class="result-item text-center">No Result Found</li>');
                        } else{
                            ul.html('');
                            $('#addquestions #adddataForm > .modal-body').css('min-height','404px');
                            if(type=="question") {
                                data.forEach(function(item) {
                                    if(item['question']!=undefined || item['question']!="undefined") {
                                    ul.append('<li id="drag'+item['id']+'" class="result-item">'+item['question']+' <button type="button" class="btn btn-primary" onclick="dataModal(this,event,'+item['id']+','+item_id+',\''+type+'\')" data-toggle="modal" data-target="#dataModal">Add</button></li>');
                                }
                                });
                            }if(type=="lesson") {
                                data.forEach(function(item) {
                                    if(item['title']!=undefined) {
                                    ul.append('<li id="drag'+item['id']+'" class="result-item">'+item['title']+' <button type="button" class="btn btn-primary" onclick="insertitem(this,event,'+item['id']+','+item_id+',\''+type+'\')">Add</button></li>');
                                }
                                });
                            } else{
                                data.forEach(function(item) {
                                    if(item['title']!=undefined) {
                                    ul.append('<li id="drag'+item['id']+'" class="result-item">'+item['title']+' <button type="button" class="btn btn-primary" onclick="itemModal(this,event,'+item['id']+','+item_id+',\''+type+'\')" data-toggle="modal" data-target="#itemModal">Add</button></li>');
                                    }
                                });
                            } 
                        }
                    },error:function(error){
                        console.log('error');
                        console.log(error);
                    }
                });
            });
            $(document).click(function(event) { 
              $target = $(event.target);
              if(!$target.closest('#searchbox-div').length && 
              $('#searchbox-div > .search-results').is(":visible")) {
                $('#searchbox-div > .search-results').hide();
              }if(!$target.closest('#modal-searchbox-div').length && 
              $('#modal-searchbox-div > .modal-search-results').is(":visible")) {
                $('#modal-searchbox-div > .modal-search-results').hide();
                $('#addquestions #adddataForm > .modal-body').css('min-height','auto');
              }        
            });
            $(document).on('click', '#save_timeline,#save_timeline1', function (e) {
                e.preventDefault();
                var list = [];
                $('ul.sorter li').each(function (key, value) {
                    key++;
                    var val = $(value).find('span').data('id');
                    list.push({id: val, sequence: key});
                });

                $.ajax({
                    method: 'POST',
                    url: "{{route('admin.courses.saveSequence')}}",
                    data: {
                        _token: '{{csrf_token()}}',
                        list: list,
                        showhidelessons:$('#showhidelessons').val(),
                        courseid:{{$course->id}}
                    }
                }).done(function () {
                    location.reload();
                });
            });
        });
        function tashtimeline(el,e) {
            e.preventDefault();
            el.innerHTML='<i class="fa fa-spinner fa-spin"></i>';
            $.ajax({
                method: 'GET',
                url: el.getAttribute("href"),
            }).done(function () {
                el.parentElement.parentElement.remove();
                toastr.success("Successfully Deleted.",'Success');
            });
        }
        function additem(el,item){
            $('#addquestions #adddataForm > #item_id').val(item);
        }
        $('#showhidelessons').on('click',function(){
        if($(this).is(':checked'))
        {
          $('ul.sorter li.lessons_li').addClass('hide');
          $(this).val(1);
        }else
        {
         $('ul.sorter li.lessons_li').removeClass('hide');
         $(this).val(0);
        }
        });
    </script>
    @endif
@endpush