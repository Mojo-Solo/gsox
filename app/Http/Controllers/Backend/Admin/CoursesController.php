<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Auth\User;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Media;
use App\Models\Question;
use App\Models\Chapter;
use App\Models\Topic;
use function foo\func;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCoursesRequest;
use App\Http\Requests\Admin\UpdateCoursesRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Yajra\DataTables\Facades\DataTables;
use DB;

class CoursesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!Gate::allows('course_access')) {
            return abort(401);
        }
        if(auth()->user()->hasRole('supervisor')) {
            return view('backend.courses.supervisor-courses');
        }
        return view('backend.courses.index');
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $courses = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('course_delete')) {
                return abort(401);
            }
            $courses = Course::onlyTrashed()->whereHas('Client')->ofSupervisor()->orderBy('created_at', 'desc')->get();
            if(auth()->user()->hasRole('supervisor')) {
                $courses=array();
            }

        } elseif (request('Supervisor_id') != "" && !auth()->user()->hasRole('supervisor')) {
            $id = request('Supervisor_id');
            $courses = Course::ofSupervisor()->whereHas('Client')->whereHas('Supervisors', function ($q) use ($id) {
                    $q->where('course_user.user_id', '=', $id);
                })->orderBy('created_at', 'desc')->get();
        } elseif (request('cat_id') != "" && !auth()->user()->hasRole('supervisor')) {
            $id = request('cat_id');
            $courses = Course::ofSupervisor()->whereHas('Client')->where('client_id', '=', $id)->orderBy('created_at', 'desc')->get();
        } elseif(auth()->user()->hasRole('supervisor') && !empty(auth()->user()->client_id)) {
            $courses = Course::whereHas('Client')->where('client_id',auth()->user()->client_id)->orderBy('created_at', 'desc')->get();
        }elseif(auth()->user()->hasRole('supervisor') && empty(auth()->user()->client_id)) {
            $courses = array();
        } else {
            $courses = Course::ofSupervisor()->whereHas('Client')->orderBy('created_at', 'desc')->get();

        }


        if (auth()->user()->can('course_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('course_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('lesson_delete')) {
            $has_delete = true;
        }

        
        return DataTables::of($courses)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.courses', 'label' => 'lesson', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.courses.show', ['course' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.courses.edit', ['course' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.courses.destroy', ['course' => $q->id])])
                        ->render();
                    $view .= $delete;
                }
                if(!auth()->user()->hasRole('supervisor')) {
                    if($q->published == 1){
                        $type = 'action-unpublish';
                    }else{
                        $type = 'action-publish';
                    }
                    $view .= view('backend.datatable.'.$type)->with(['route' => route('admin.courses.publish', ['course' => $q->id])])->render();
                }
                return $view;

            })
            ->editColumn('Supervisors', function ($q) {
                $Supervisors = "";
                foreach ($q->Supervisors as $singleSupervisors) {
                    $Supervisors .= '<span class="label label-info label-many">' . $singleSupervisors->name . ' </span>';
                }
                return $Supervisors;
            })
            ->addColumn('lessons', function ($q) {
                $lesson = '<a href="' . route('admin.lessons.create', ['course_id' => $q->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i></a>  <a href="' . route('admin.lessons.index', ['course_id' => $q->id]) . '" class="btn mb-1 btn-warning text-white"><i class="fa fa-arrow-circle-right"></a>';
                return $lesson;
            })
            ->editColumn('course_image', function ($q) {
                return ($q->course_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->course_image) . '">' : 'N/A';
            })
            ->editColumn('status', function ($q) {
                $text = "";
                $text = ($q->published == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-dark p-1 mr-1' >" . trans('labels.backend.courses.fields.published') . "</p>" : "";
                $text .= ($q->featured == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-warning p-1 mr-1' >" . trans('labels.backend.courses.fields.featured') . "</p>" : "";
                $text .= ($q->trending == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-success p-1 mr-1' >" . trans('labels.backend.courses.fields.trending') . "</p>" : "";
                $text .= ($q->popular == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.courses.fields.popular') . "</p>" : "";
                return $text;
            })
            ->editColumn('price', function ($q) {
                if ($q->free == 1) {
                    return trans('labels.backend.courses.fields.free');
                }
                return $q->price;
            })
            ->addColumn('Client', function ($q) {
                return $q->Client->name.','.$q->Client->slug;
            })
            ->rawColumns(['Supervisors', 'lessons', 'course_image', 'actions', 'status'])
            ->make();
    }


    /**
     * Show the form for creating new Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('course_create') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        
        $Supervisors = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');

        $Clients = Client::where('status', '=', 1)->pluck('name', 'id');

        return view('backend.courses.create', compact('Supervisors', 'Clients'));
    }

    /**
     * Store a newly created Course in storage.
     *
     * @param  \App\Http\Requests\StoreCoursesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCoursesRequest $request)
    {
        if (!Gate::allows('course_create') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }

        $request->all();

        $request = $this->saveFiles($request);

        $course = Course::create($request->all());

        //Saving  videos
        if ($request->media_type != "") {
            $model_type = Course::class;
            $model_id = $course->id;
            $size = 0;
            $media = '';
            $url = '';
            $video_id = '';
            $name = $course->title . ' - video';

            if (($request->media_type == 'youtube') || ($request->media_type == 'vimeo')) {
                $video = $request->video;
                $url = $video;
                $video_id = array_last(explode('/', $request->video));
                $media = Media::where('url', $video_id)
                    ->where('type', '=', $request->media_type)
                    ->where('model_type', '=', 'App\Models\Course')
                    ->where('model_id', '=', $course->id)
                    ->first();
                $size = 0;

            } elseif ($request->media_type == 'upload') {
                if (\Illuminate\Support\Facades\Request::hasFile('video_file')) {
                    $file = \Illuminate\Support\Facades\Request::file('video_file');
                    $filename = time() . '-' . $file->getClientOriginalName();
                    $size = $file->getSize() / 1024;
                    $path = public_path() . '/storage/uploads/';
                    $file->move($path, $filename);

                    $video_id = $filename;
                    $url = asset('storage/uploads/' . $filename);

                    $media = Media::where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Lesson')
                        ->where('model_id', '=', $course->id)
                        ->first();
                }
            } else if ($request->media_type == 'embed') {
                $url = $request->video;
                $filename = $course->title . ' - video';
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


        if (($request->slug == "") || $request->slug == null) {
            $course->slug = str_slug($request->title);
            $course->save();
        }
        if ((int)$request->price == 0) {
            $course->price = NULL;
            $course->save();
        }


        $Supervisors = \Auth::user()->isAdmin() ? array_filter((array)$request->input('Supervisors')) : [\Auth::user()->id];
        $course->Supervisors()->sync($Supervisors);


        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('course_edit') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $Supervisors = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');
        $Clients = Client::where('status', '=', 1)->pluck('name', 'id');


        $course = Course::findOrFail($id);

        return view('backend.courses.edit', compact('course', 'Supervisors', 'Clients'));
    }

    /**
     * Update Course in storage.
     *
     * @param  \App\Http\Requests\UpdateCoursesRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCoursesRequest $request, $id)
    {
        if (!Gate::allows('course_edit') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $course = Course::findOrFail($id);
        $request = $this->saveFiles($request);

        //Saving  videos
        if ($request->media_type != "" || $request->media_type  != null) {
            if($course->mediavideo){
                $course->mediavideo->delete();
            }
            $model_type = Course::class;
            $model_id = $course->id;
            $size = 0;
            $media = '';
            $url = '';
            $video_id = '';
            $name = $course->title . ' - video';
            $media = $course->mediavideo;
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
                    $filename = $course->title . ' - video';
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

                if ($request->video_file != null) {

                    $media = Media::where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Course')
                        ->where('model_id', '=', $course->id)
                        ->first();

                    if ($media == null) {
                        $media = new Media();
                    }
                    $media->model_type = $model_type;
                    $media->model_id = $model_id;
                    $media->name = $name;
                    $media->url = url('storage/uploads/'.$request->video_file);
                    $media->type = $request->media_type;
                    $media->file_name = $request->video_file;
                    $media->size = 0;
                    $media->save();

                }
            }
        }


        $course->update($request->all());
        if (($request->slug == "") || $request->slug == null) {
            $course->slug = str_slug($request->title);
            $course->save();
        }
        if ((int)$request->price == 0) {
            $course->price = NULL;
            $course->save();
        }

        $Supervisors = \Auth::user()->isAdmin() ? array_filter((array)$request->input('Supervisors')) : [\Auth::user()->id];
        $course->Supervisors()->sync($Supervisors);

        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('course_view')) {
            return abort(401);
        }
        $Supervisors = User::get()->pluck('name', 'id');
        $lessons = \App\Models\Lesson::where('course_id', $id)->get();
        $tests = \App\Models\Test::where('course_id', $id)->get();

        $course = Course::findOrFail($id);
        $courseTimeline = $course->courseTimeline()->orderBy('sequence', 'asc')->get();
        $topics=Topic::where('course_id',$course->id)->get()->pluck('title', 'id')->toArray();
        return view('backend.courses.show', compact('course', 'lessons', 'tests', 'courseTimeline','topics'));
    }


    /**
     * Remove Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('course_delete') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $course = Course::findOrFail($id);
        if ($course->students->count() >= 1) {
            return redirect()->route('admin.courses.index')->withFlashDanger(trans('alerts.backend.general.delete_warning'));
        } else {
            $course->delete();
        }


        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Course at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('course_delete') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Course::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('course_delete') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('course_delete') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();

        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Permanently save Sequence from storage.
     *
     * @param  Request
     */
    public function saveSequence(Request $request)
    {
        if (!Gate::allows('course_edit') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }
        $course=Course::findOrFail($request->courseid);
        $course->showhidelessons=$request->showhidelessons;
        $course->save();
        foreach ($request->list as $item) {
            $courseTimeline = CourseTimeline::find($item['id']);
            $courseTimeline->sequence = $item['sequence'];
            $courseTimeline->save();
        }

        return 'success';
    }


    /**
     * Publish / Unpublish courses
     *
     * @param  Request
     */
    public function publish($id)
    {
        if (!Gate::allows('course_edit') || auth()->user()->hasRole('supervisor')) {
            return abort(401);
        }

        $course = Course::findOrFail($id);
        if ($course->published == 1) {
            $course->published = 0;
        } else {
            $course->published = 1;
        }
        $course->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    public function searchquestion() {
        $result=Question::where('question', 'like', '%' . request()->get('query') . '%')->get();
        return json_encode($result);
    }
    public function savequestion(Request $request){
        $question_id=$request->qid;
        $course_id=$request->cid;
        $test_id=$request->tid;
        $question=Question::findOrFail($question_id);
        
        if($request->type=="test") {
            if(!empty($test_id)) {
                if(DB::table('question_test')->where('question_id',$question_id)->where('test_id',$test_id)->count())
                {
                    return json_encode(array('error'=>'Question already exist in test'));
                }
                $question_test=DB::table('question_test')->insert(array('question_id'=>$question_id,'test_id'=>$test_id));
                return json_encode(array('success'=>'Question added successfully'));
            } else {
                return json_encode(array('error'=>'You do not select any test'));
            }
        } else {
            if(CourseTimeline::where('course_id',$course_id)->where('model_type','App\Models\Question')->where('model_id',$question_id)->count())
            {
                return json_encode(array('error'=>'Question already exist in timeline'));
            }
            if($request->topic_id) {
                $question->review_topic_id=$request->topic_id;
                $question->save();
            }
            
            $maxValue = (int)CourseTimeline::where('course_id',$course_id)->max('sequence')+1;
            
            $courseTimeline=CourseTimeline::create(array('model_type'=>'App\Models\Question','model_id'=>$question_id,'course_id'=>$course_id,'sequence'=>$maxValue));
            return json_encode(array('timeline'=>$courseTimeline->id,'sequence'=>$maxValue,'question'=>$question->question,'qid'=>$question_id));
        }
        
        
    }
    
    public function savecontent(Request $request){
        $itemid=$request->itemid;
        $type=$request->type;
        $course_id=$request->cid;
        $maxValue = (int)CourseTimeline::where('course_id',$course_id)->max('sequence')+1;
        
        if($type=="topic") {
            $result=\App\Models\Topic::findOrFail($itemid);
            $lesson_id=$request->lid;
            $result->lesson_id=$lesson_id;
            $result->save();
            $courseTimeline=CourseTimeline::create(array('model_type'=>'App\Models\Topic','model_id'=>$result->id,'course_id'=>$course_id,'sequence'=>$maxValue));

            return json_encode(array('timeline'=>$courseTimeline->id,'sequence'=>$maxValue,'question'=>$result->title,'qid'=>$itemid));
        }
        elseif($type=="lesson") {
            $result=\App\Models\Lesson::findOrFail($itemid);
            $courseTimeline=CourseTimeline::create(array('model_type'=>'App\Models\Lesson','model_id'=>$result->id,'course_id'=>$course_id,'sequence'=>$maxValue));

            return json_encode(array('timeline'=>$courseTimeline->id,'sequence'=>$maxValue,'question'=>$result->title,'qid'=>$itemid));
        }
        elseif($type=="test") {
            $result=\App\Models\Test::findOrFail($itemid);
            $lesson_id=$request->lid;
            $result->lesson_id=$lesson_id;
            $result->save();
            $courseTimeline=CourseTimeline::create(array('model_type'=>'App\Models\Test','model_id'=>$result->id,'course_id'=>$course_id,'sequence'=>$maxValue));

            return json_encode(array('timeline'=>$courseTimeline->id,'sequence'=>$maxValue,'question'=>$result->title,'qid'=>$itemid));
        }else {
            return json_encode('');
        }
        
    }
    
    public function addContents(Request $request) {
        $type=$request->modeltype;
        $query=$request->keyword;
        if($type=="question"){
            $result=Question::where('question', 'like', '%' . $query . '%')->get();
        }
        elseif($type=="topic") {
            $result=\App\Models\Topic::where('title', 'like', '%' . $query . '%')->get();
        }
        elseif($type=="lesson") {
            $result=\App\Models\Lesson::where('title', 'like', '%' . $query . '%')->get();
        }
        elseif($type=="test") {
            $result=\App\Models\Test::where('title', 'like', '%' . $query . '%')->get();
        }
        else {
            return json_encode('');
        }
        return json_encode($result);
    }
    
}
