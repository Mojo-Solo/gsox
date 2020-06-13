<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreClientsRequest;
use App\Http\Requests\Admin\UpdateClientsRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use File;

class ClientsController extends Controller
{

    use FileUploadTrait;
    public function __construct()
    {
        if(!auth()->user()->isAdmin())
            return abort(401);
    }
    /**
     * Display a listing of Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('client_access')) {
            return abort(401);
        }


        if (request('show_deleted') == 1) {
            if (!Gate::allows('client_delete')) {
                return abort(401);
            }
            $Clients = Client::onlyTrashed()->get();
        } else {
            $Clients = Client::all();
        }

        return view('backend.Clients.index', compact('Clients'));
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
        $Clients = "";


        if (request('show_deleted') == 1) {
            if (!Gate::allows('client_delete')) {
                return abort(401);
            }
            $Clients = Client::onlyTrashed()->orderBy('created_at', 'desc')->get();
        } else {
            $Clients = Client::orderBy('created_at', 'desc')->get();
        }

        if (auth()->user()->can('client_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('client_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('client_delete')) {
            $has_delete = true;
        }

        return DataTables::of($Clients)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.Clients', 'label' => 'Client', 'value' => $q->id]);
                }
//                if ($has_view) {
//                    $view = view('backend.datatable.action-view')
//                        ->with(['route' => route('admin.Clients.show', ['Client' => $q->id])])->render();
//                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.Clients.edit', ['Client' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.Clients.destroy', ['Client' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                $view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['cat_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';


                return $view;

            })
            ->editColumn('icon', function ($q) {
                if (empty($q->image) && $q->icon != "") {
                    return '<i style="font-size:40px;" class="'.$q->icon.'"></i>';
                }elseif(!empty($q->image)){
                    return '<img style="height:50px;width:50px;" class="img-responsive" src="'.url("public/images/".$q->image).'" />';
                }else{
                    return 'N/A';
                }
            })
            ->editColumn('courses', function ($q) {
                return $q->courses->count();
            })
            ->editColumn('status', function ($q) {
                return ($q->status == 1) ? "Enabled" : "Disabled";
            })
            ->rawColumns(['actions', 'icon'])
            ->make();
    }

    /**
     * Show the form for creating new Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('client_create')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofSupervisor()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        return view('backend.Clients.create', compact('courses', 'lessons'));
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param  \App\Http\Requests\StoreClientsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientsRequest $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        if (!Gate::allows('client_create')) {
            return abort(401);
        }
        $Client = Client::where('slug','=',str_slug($request->name))->first();
        if($Client == null){
            $Client = new  Client();
        }
        $Client->name = $request->name;
        $Client->slug = str_slug($request->name);
        $Client->icon = $request->icon;
        if($request->hasFile('image')) {
            $imgfile = $request->file('image');
            $imgpath = 'public/images';
            File::makeDirectory($imgpath, $mode = 0777, true, true);
            $name = time()."_".$imgfile->getClientOriginalName();
            $success = $imgfile->move(public_path('images'), $name);
            $Client->image=$name;           
        }
        $Client->save();

        return redirect()->route('admin.Clients.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('client_edit')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofSupervisor()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        $Client = Client::findOrFail($id);

        return view('backend.Clients.edit', compact('Client', 'courses', 'lessons'));
    }

    /**
     * Update Client in storage.
     *
     * @param  \App\Http\Requests\UpdateClientsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientsRequest $request, $id)
    {
        if (!Gate::allows('client_edit')) {
            return abort(401);
        }
        $Client = Client::findOrFail($id);
        $Client->name = $request->name;
        $Client->slug = str_slug($request->name);
        $Client->icon = $request->icon;
        if($request->hasFile('image')) {
            $imgfile = $request->file('image');
            $imgpath = 'public/images';
            File::makeDirectory($imgpath, $mode = 0777, true, true);
            $name = time()."_".$imgfile->getClientOriginalName();
            $success = $imgfile->move(public_path('images'), $name);
            $old_image=public_path('images')."\\".$Client->image;
            if (File::exists($old_image)) {
                File::delete($old_image);
            }
            $Client->image=$name;
        }
        $Client->save();

        return redirect()->route('admin.Clients.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('client_view')) {
            return abort(401);
        }
        $Client = Client::findOrFail($id);

        return view('backend.Clients.show', compact('Client'));
    }


    /**
     * Remove Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('client_delete')) {
            return abort(401);
        }
        $Client = Client::findOrFail($id);
        $Client->delete();

        return redirect()->route('admin.Clients.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Client at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('client_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Client::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('client_delete')) {
            return abort(401);
        }
        $Client = Client::onlyTrashed()->findOrFail($id);
        $Client->restore();

        return redirect()->route('admin.Clients.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('client_delete')) {
            return abort(401);
        }
        $Client = Client::onlyTrashed()->findOrFail($id);
        $Client->forceDelete();

        return redirect()->route('admin.Clients.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
