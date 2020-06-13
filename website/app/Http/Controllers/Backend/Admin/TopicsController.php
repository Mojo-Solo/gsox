<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use App\Models\Topic;
use App\Models\Media;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTopicsRequest;
use App\Http\Requests\Admin\UpdateTopicsRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Yajra\DataTables\Facades\DataTables;

class TopicsController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Topic.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Gate::allows('topics_access')) {
           // return abort(401);
        }
        $courses = Course::has('category')->ofTeacher()->pluck('title', 'id')->prepend('Please select', '');
        $lessons = Lesson::where('course_id', $request->course_id)->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.topics.index', compact('courses', 'lessons')); 
    }

    /**
     * Display a listing of Topics via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $topics = "";
        $topics = Topic::where('course_id', $request->course_id)->get();
        if ($request->lesson_id != "") {
            //$topics = $topics->orderBy('created_at', 'desc')->get();
        }

        if ($request->show_deleted == 1) {
            if (!Gate::allows('topic_delete')) {
                //return abort(401);
            }
            $topics = Topic::where('course_id', $request->course_id)->orderBy('position', 'desc')->onlyTrashed()->get();
        }

        if ($request->course_id > 0 ){
            if (!Gate::allows('topic_delete')) {
                //return abort(401);
            }
            $topics = Topic::where('course_id', $request->course_id)->orderBy('position', 'desc')->get();
        }

        if ($request->lesson_id > 0 ){
            if (!Gate::allows('topic_delete')) {
                //return abort(401);
            }
            $topics = Topic::where('lesson_id', $request->lesson_id)->orderBy('position', 'desc')->get();
        }

        if($request->lesson_id > 0  && $request->course_id > 0 ){
            $topics = Topic::where('lesson_id', $request->lesson_id)->where('course_id', $request->course_id)->orderBy('position', 'desc')->get();
        }


        /*if (auth()->user()->can('topic_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('lesson_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('topic_delete')) {
            $has_delete = true;
        }*/
        $has_view = true;
        $has_edit = true;
        $has_delete = true;

        return DataTables::of($topics)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.topics', 'label' => 'topic', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.topics.show', ['topic' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.topics.edit', ['topic' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.topics.destroy', ['topic' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                if (auth()->user()->can('test_view')) {
                    if ($q->test != "") {
                        $view .= '<a href="' . route('admin.tests.index', ['topic_id' => $q->id]) . '" class="btn btn-success btn-block mb-1">' . trans('labels.backend.tests.title') . '</a>';
                    }
                }

                return $view;

            })
            ->editColumn('course', function ($q) {
                return ($q->course) ? $q->course->title : 'N/A';
            })
            ->editColumn('topic_image', function ($q) {
                return ($q->topic_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->topic_image) . '">' : 'N/A';
            })
            ->editColumn('free_topic', function ($q) {
                return ($q->free_topic == 1) ? "Yes" : "No";
            })
            ->editColumn('published', function ($q) {
                return ($q->published == 1) ? "Yes" : "No";
            })
            ->rawColumns(['topic_image', 'actions'])
            ->make();
    }

    /**
     * Show the form for creating new Topic.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }
        $courses = Course::has('category')->ofTeacher()->get()->pluck('title', 'id')->prepend('Please select', '');
        $lessons = Lesson::where('course_id', $request->course_id)->get()->pluck('title', 'id')->prepend('Please select', '');
        $tests = \App\Models\Test::get()->pluck('title', 'id')->toArray();
        return view('backend.topics.create', compact('lessons', 'tests'));
    }

    /**
     * Store a newly created Topic in storage.
     *
     * @param  \App\Http\Requests\StoreTopicsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTopicsRequest $request)
    {
        if (!Gate::allows('lesson_create')) {
           return abort(401);
        }

        $topic = Topic::create($request->except('downloadable_files', 'topic_image')
            + ['position' => Topic::where('lesson_id', $request->lesson_id)->max('position') + 1]);


        //Saving  videos
        if ($request->media_type != "") {
            $model_type = Topic::class;
            $model_id = $topic->id;
            $size = 0;
            $media = '';
            $url = '';
            $video_id = '';
            $name = $topic->title . ' - video';

            if (($request->media_type == 'youtube') || ($request->media_type == 'vimeo')) {
                $video = $request->video;
                $url = $video;
                $video_id = array_last(explode('/', $request->video));
                $media = Media::where('url', $video_id)
                    ->where('type', '=', $request->media_type)
                    ->where('model_type', '=', 'App\Models\Topic')
                    ->where('model_id', '=', $topic->id)
                    ->first();
                $size = 0;

            } elseif ($request->media_type == 'upload') {
                if (\Illuminate\Support\Facades\Request::hasFile('video_file')) {
                    $file = \Illuminate\Support\Facades\Request::file('video_file');
                    $filename = time() . '-' . $file->getClientOriginalName();
                    $size = null; //$file->getSize() / 1024;
                    $path = public_path() . '/storage/uploads/';
                    $file->move($path, $filename);

                    $video_id = $filename;
                    $url = asset('storage/uploads/' . $filename);

                    $media = Media::where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Topic')
                        ->where('model_id', '=', $topic->id)
                        ->first();
                }
            } else if ($request->media_type == 'embed') {
                $url = $request->video;
                $filename = $topic->title . ' - video';
            }

            if ($media == null) {
                $media = new Media();
                $media->model_type = $model_type;
                $media->model_id = $model_id;
                $media->name = $name;
                $media->url = $url;
                $media->type = $request->media_type;
                $media->file_name = $video_id;
                $media->size = 0;
                $media->save();
            }
        }

        if(\Illuminate\Support\Facades\Request::hasFile('add_audio')){
            $file = \Illuminate\Support\Facades\Request::file('add_audio');
            $filename = time() . '-' . $file->getClientOriginalName();
            $path = public_path() . '/storage/uploads/';
            $file->move($path, $filename);
            $url = asset('storage/uploads/' . $filename);
            $topic->topic_audio = $url;
            $topic->save();
        }

        $request = $this->saveAllFiles($request, 'downloadable_files', Topic::class, $topic, 'topic');

        if (($request->slug == "") || $request->slug == null) {
            $topic->slug = str_slug($request->title);
            $topic->save();
        }

        $sequence = 1;
        if($topic->lesson && $topic->lesson->course){
            if (count($topic->lesson->course->courseTimeline) > 0) {
                $sequence = $topic->lesson->course->courseTimeline->max('sequence');
                $sequence = $sequence + 1;
            }
        }

        if($topic->lesson_id){
            $get_lesson = Lesson::findorfail($topic->lesson_id);
            if($get_lesson){
                $topic->course_id = $get_lesson->course_id;
                $topic->save();
            }
        }

        if ($topic->published == 1) {
            $timeline = CourseTimeline::where('model_type', '=', Topic::class)
                ->where('model_id', '=', $topic->id)
                ->where('course_id', $topic->course_id)->first();
            if ($timeline == null) {
                $timeline = new CourseTimeline();
            }
            $timeline->course_id = $topic->course_id;
            $timeline->model_id = $topic->id;
            $timeline->model_type = Topic::class;
            $timeline->sequence = $sequence;
            $timeline->save();
        }

        return redirect()->route('admin.topics.index', ['lesson_id' => $request->lesson_id])->withFlashSuccess(__('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Topic.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('lesson_edit')) {
            return abort(401);
        }
        $videos = '';
        $topic = Topic::with('media')->findOrFail($id);
        $parent_topics = Topic::where('id', '!=', $topic->id)->where('lesson_id', $topic->lesson_id)->where('parent_topic', null)->orderBy('created_at', 'desc')->get()->pluck('title', 'id')->prepend('Please select', '');;
        $lessons = Lesson::where('course_id', $topic->course_id)->get()->pluck('title', 'id')->prepend('Please select', '');
        $tests = Test::where('course_id', $topic->course_id)->get()->pluck('title', 'id')->prepend('Please select', '');
        if ($topic->media) {
            $videos = $topic->media()->where('media.type', '=', 'YT')->pluck('url')->implode(',');
        }

        return view('backend.topics.edit', compact('topic', 'lessons', 'videos', 'parent_topics', 'tests'));
    }

    /**
     * Update Topic in storage.
     *
     * @param  \App\Http\Requests\UpdateTopicsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTopicsRequest $request, $id)
    {
        if (!Gate::allows('lesson_edit')) {
            //return abort(401);
        }
        $topic = Topic::findOrFail($id);
        $topic->update($request->except('downloadable_files', 'topic_image'));
        if (($request->slug == "") || $request->slug == null) {
            $topic->slug = str_slug($request->title);
            $topic->save();
        }

        //Saving  videos
        if ($request->media_type != "") {
            $model_type = Topic::class;
            $model_id = $topic->id;
            $size = 0;
            $media = '';
            $url = '';
            $video_id = '';
            $name = $topic->title . ' - video';
            $media = $topic->mediavideo;
            if ($media == "") {
                $media = new  Media();
            }
            if ($request->media_type != 'upload') {
                if (($request->media_type == 'youtube') || ($request->media_type == 'vimeo')) {
                    $video = $request->video;
                    $url = $video;
                    $video_id = array_last(explode('/', $request->video));
                    $size = 0;

                } else if ($request->media_type == 'embed') {
                    $url = $request->video;
                    $filename = $topic->title . ' - video';
                }
                $media->model_type = $model_type;
                $media->model_id = $model_id;
                $media->name = $name;
                $media->url = $url;
                $media->type = $request->media_type;
                $media->file_name = $video_id;
                $media->size = 0;
                $media->save();
            }

            if ($request->media_type == 'upload') {
                if (\Illuminate\Support\Facades\Request::hasFile('video_file')) {
                    $file = \Illuminate\Support\Facades\Request::file('video_file');
                    $filename = time() . '-' . $file->getClientOriginalName();
                    $size = null; //$file->getSize() / 1024;
                    $path = public_path() . '/storage/uploads/';
                    $file->move($path, $filename);

                    $video_id = $filename;
                    $url = asset('storage/uploads/' . $filename);

                    $media = Media::where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Topic')
                        ->where('model_id', '=', $topic->id)
                        ->first();

                    if ($media == null) {
                        $media = new Media();
                    }
                    $media->model_type = $model_type;
                    $media->model_id = $model_id;
                    $media->name = $name;
                    $media->url = $url;
                    $media->type = $request->media_type;
                    $media->file_name = $video_id;
                    $media->size = 0;
                    $media->save();

                }
            }
        }
        if($request->hasFile('add_pdf')){
            $pdf = $topic->mediaPDF;
            if($pdf){
                $pdf->delete();
            }
        }


        if(\Illuminate\Support\Facades\Request::hasFile('add_audio')){
            $file = \Illuminate\Support\Facades\Request::file('add_audio');
            $filename = time() . '-' . $file->getClientOriginalName();
            $path = public_path() . '/storage/uploads/';
            $file->move($path, $filename);
            $url = asset('storage/uploads/' . $filename);
            $topic->topic_audio = $url;
            $topic->save();
        }

        $request = $this->saveAllFiles($request, 'downloadable_files', Topic::class, $topic, 'topic');

        $sequence = 1;
        if($topic->lesson && $topic->lesson->course){
            if (count($topic->lesson->course->courseTimeline) > 0) {
                $sequence = $topic->lesson->course->courseTimeline->max('sequence');
                $sequence = $sequence + 1;
            }
        }

        if (($topic->published != 1) && ((int)$request->published == 1)) {
            $timeline = CourseTimeline::where('model_type', '=', Topic::class)
                ->where('model_id', '=', $topic->id)
                ->where('course_id', $request->course_id)->first();
            if ($timeline == null) {
                $timeline = new CourseTimeline();
            }
            $timeline->course_id = $request->course_id;
            $timeline->model_id = $topic->id;
            $timeline->model_type = Topic::class;
            $timeline->sequence = $sequence;
            $timeline->save();
        }


        return redirect()->route('admin.topics.index', ['course_id' => $request->course_id])->withFlashSuccess(__('alerts.backend.general.updated'));
    }


    /**
     * Display Topic.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('topic_view')) {
            //return abort(401);
        }
        $courses = Course::get()->pluck('title', 'id')->prepend('Please select', '');

        $tests = Test::where('topic_id', $id)->get();

        $topic = Topic::findOrFail($id);


        return view('backend.topics.show', compact('topic', 'tests', 'courses'));
    }


    /**
     * Remove Topic from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('topic_delete')) {
            //return abort(401);
        }
        $topic = Topic::findOrFail($id);
        $topic->delete();

        return back()->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Topic at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('topic_delete')) {
            //return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Topic::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Topic from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('topic_delete')) {
            //return abort(401);
        }
        $topic = Topic::onlyTrashed()->findOrFail($id);
        $topic->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Topic from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('topic_delete')) {
            //return abort(401);
        }
        $topic = Topic::onlyTrashed()->findOrFail($id);

        if(File::exists(public_path('/storage/uploads/'.$topic->topic_image))) {
            File::delete(public_path('/storage/uploads/'.$topic->topic_image));
            File::delete(public_path('/storage/uploads/thumb/'.$topic->topic_image));
        }

        $timelineStep = CourseTimeline::where('model_id', '=', $id)
            ->where('course_id', '=', $topic->course->id)->first();
        if($timelineStep){
            $timelineStep->delete();
        }

        $topic->forceDelete();



        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
