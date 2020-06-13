<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Models\Media;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChaptersRequest;
use App\Http\Requests\Admin\UpdateChaptersRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Yajra\DataTables\Facades\DataTables;

class ChaptersController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Lesson.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Gate::allows('chapter_access')) {
            return abort(401);
        }
        $course_ids = Course::has('Client')->ofSupervisor()->pluck('id')->toArray();
        $chapters=Chapter::whereIn('course_id',$course_ids)->get();
        return view('backend.chapters.index', compact('chapters'));
    }

    /**
     * Display a listing of Lessons via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $chapters = "";
        $course_ids = Course::has('Client')->ofSupervisor()->pluck('id')->toArray();
        $chapters=Chapter::whereIn('course_id',$course_ids)->get();
        // if ($request->id != "") {
        //     $chapters = Chapter::where('id', (int)$request->id)->orderBy('created_at', 'desc')->get();
        // }

        // if ($request->show_deleted == 1) {
        //     if (!Gate::allows('chapter_delete')) {
        //         return abort(401);
        //     }
        //     $chapters = Chapter::query()->orderBy('created_at', 'desc')->onlyTrashed()->get();
        // }


        if (auth()->user()->can('chapter_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('chapter_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('chapter_delete')) {
            $has_delete = true;
        }

        return DataTables::of($chapters)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.chapters', 'label' => 'lesson', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.chapters.show', ['lesson' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.chapters.edit', ['lesson' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.chapters.destroy', ['lesson' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                return $view;

            })->editColumn('published', function ($q) {
                return ($q->published == 1) ? "Yes" : "No";
            })
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Show the form for creating new Lesson.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('chapter_create')) {
            return abort(401);
        }
        $courses = Course::has('Client')->ofSupervisor()->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.chapters.create', compact('courses'));
    }

    /**
     * Store a newly created Lesson in storage.
     *
     * @param  \App\Http\Requests\StoreLessonsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChaptersRequest $request)
    {
        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }
        if(!empty($request->lessons))
            $lesson_ids=implode(",",$request->lessons);
        else
            $lesson_ids='';
        $chapter = Chapter::create(['title' => $request->title,"lesson_ids"=>$lesson_ids,'course_id'=>$request->course_id,"published"=>$request->published]);
        if (($request->slug == "") || $request->slug == null) {
            $chapter->slug = str_slug($request->title);
            $chapter->save();
        }

        return redirect()->route('admin.chapters.index')->withFlashSuccess(__('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Lesson.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('chapter_edit')) {
            return abort(401);
        }
        $chapter = Chapter::findOrFail($id);
        $course = Course::findOrFail($chapter->course_id);
        if(!empty($chapter->lesson_ids))
            $chapter_lesson_ids=explode(",",$chapter->lesson_ids);
        else
            $chapter_lesson_ids=array('');
        $ids=array();
        $chapters=Chapter::where('course_id',$chapter->course_id)->where('id','!=',$id)->whereNotNull('lesson_ids')->get();
        foreach ($chapters as $key => $chapter1) {
            $lesson_ids=explode(",", $chapter1->lesson_ids);
            for($i=0; $i<count($lesson_ids); $i++) {
                // if(!in_array($lesson_ids[$i], $chapter_lesson_ids)) {
                    array_push($ids,$lesson_ids[$i]);
                // }
            }
        }
        $lessons=Lesson::whereNotIn('id',$ids)->where('course_id',$chapter->course_id)->select('id','title')->get();
        $lesson_ids=explode(",", $chapter->lesson_ids);
        return view('backend.chapters.edit', compact('chapter','course', 'lessons','lesson_ids'));
    }

    /**
     * Update Lesson in storage.
     *
     * @param  \App\Http\Requests\UpdateLessonsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChaptersRequest $request, $id)
    {
        if (!Gate::allows('chapter_edit')) {
            return abort(401);
        }
        $chapter = Chapter::findOrFail($id);
        $lesson_ids=implode(",",$request->lessons);
        $chapter->update(['title' => $request->title,"lesson_ids"=>$lesson_ids,"published"=>$request->published]);
        if (($request->slug == "") || $request->slug == null) {
            $chapter->slug = str_slug($request->title);
            $chapter->save();
        }

        return redirect()->route('admin.chapters.index')->withFlashSuccess(__('alerts.backend.general.updated'));
    }


    /**
     * Display Lesson.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('chapter_view')) {
            return abort(401);
        }
        $chapter = Chapter::findOrFail($id);
        $course=Course::findOrFail($chapter->course_id);
        $lessons = Lesson::whereIn('id',explode(",", $chapter->lesson_ids))->get();


        return view('backend.chapters.show', compact('lessons', 'chapter','course'));
    }


    /**
     * Remove Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('chapter_delete')) {
            return abort(401);
        }
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return back()->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Lesson at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('chapter_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Chapter::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('chapter_delete')) {
            return abort(401);
        }
        $lesson = Lesson::onlyTrashed()->findOrFail($id);
        $lesson->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('chapter_delete')) {
            return abort(401);
        }
        $lesson = Lesson::onlyTrashed()->findOrFail($id);

        if(File::exists(public_path('/storage/uploads/'.$lesson->lesson_image))) {
            File::delete(public_path('/storage/uploads/'.$lesson->lesson_image));
            File::delete(public_path('/storage/uploads/thumb/'.$lesson->lesson_image));
        }

        $timelineStep = CourseTimeline::where('model_id', '=', $id)
            ->where('course_id', '=', $lesson->course->id)->first();
        if($timelineStep){
            $timelineStep->delete();
        }

        $lesson->forceDelete();



        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
    public function getLessons($id) {
        $ids=array();
        $chapters=Chapter::where('course_id',$id)->whereNotNull('lesson_ids')->get();
        foreach ($chapters as $key => $chapter) {
            $lesson_ids=explode(",", $chapter->lesson_ids);
            for($i=0; $i<count($lesson_ids); $i++) {
                array_push($ids,$lesson_ids[$i]);
            }
        }
        $lessons=Lesson::whereNotIn('id',$ids)->where('course_id',$id)->select('id','title')->get();
        $html='';
            foreach($lessons as $key => $lesson){
                $html.='<option value="'.$lesson->id.'">'.$lesson->title.'</option>';
            }
        // $html.='</select>';
        echo $html;
    }
}
