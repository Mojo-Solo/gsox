<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoresiteadminusersRequest;
use App\Http\Requests\Admin\UpdatesiteadminusersRequest;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Models\Client;
use App\Models\Course;

class SiteAdminUsersController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (request('show_deleted') == 1) {

            $users = User::role('student')->onlyTrashed()->get();
        } else {
            $users = User::role('student')->get();
        }

        return view('backend.siteadminusers.index', compact('users'));
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
        $siteadminusers = NULL;
        if (request('show_deleted') == 1) {
            //$siteadminusers = User::role('student')->onlyTrashed()->orderBy('created_at', 'desc')->get();
        } else {
            //$siteadminusers = User::role('student')->orderBy('created_at', 'desc')->get();
        }

        $client_id = \Auth::user()->client_id;
        if($client_id > 0){
            $get_client = Client::findorfail($client_id);
            $courses_list = Course::withTrashed()->get();
            //dd($courses_list);
            foreach($courses_list as $course){
                if($siteadminusers == null){
                    $siteadminusers =  $course->students;
                }else{
                    $siteadminusers =  $siteadminusers->merge($course->students);
                }
            }
        }
        
        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }


        return DataTables::of($siteadminusers)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.siteadminusers', 'label' => 'vendor', 'value' => $q->id]);
                }
//                if ($has_view) {
//                    $view = view('backend.datatable.action-view')
//                        ->with(['route' => route('admin.siteadminusers.show', ['vendor' => $q->id])])->render();
//                }

                //if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.getusers.edit', ['vendor' => $q->id])])
                        ->render();
                    $view .= $edit;
                //}

                //if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.getusers.destroy', ['vendor' => $q->id])])
                        ->render();
                    $view .= $delete;
                //}

               // $view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['vendor_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;

            })
            ->editColumn('status', function ($q) {
                return ($q->active == 1) ? "Enabled" : "Disabled";
            })
            ->rawColumns(['actions', 'image'])
            ->make();
    }

    /**
     * Show the form for creating new Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$clients = Client::get()->pluck('name', 'id')->prepend('Please select', '');;
       // return view('backend.siteadminusers.create', compact('clients'));
       return redirect()->route('admin.getusers.index');
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param  \App\Http\Requests\StoresiteadminusersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresiteadminusersRequest $request)
    {
//        $request = $this->saveFiles($request);

        $user = User::create($request->all());
        $user->confirmed = 1;
        $user->avatar_type = 'storage';
        if ($request->image) {
            $user->avatar_location = $request->image->store('/avatars', 'public');
        }
        $user->save();

        $user->assignRole('vendor');

        return redirect()->route('admin.getusers.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $user = User::findOrFail($id);
        $clients = Client::get()->pluck('name', 'id')->prepend('Please select', '');
        $get_vendors = User::role('vendor')->get();
        $vendors = array();
        if($get_vendors ){
            $vendors[''] = 'Please select';
            foreach($get_vendors as $vendor){
                $vendors[$vendor->id] = $vendor->name.' - '.$vendor->city.', '.$vendor->state;
            }
        }
        return view('backend.siteadminusers.edit', compact('user', 'clients', 'vendors'));
    }

    /**
     * Update Client in storage.
     *
     * @param  \App\Http\Requests\UpdatesiteadminusersRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesiteadminusersRequest $request, $id)
    {
        //$request = $this->saveFiles($request);

        $vendor = User::findOrFail($id);
        $vendor->update($request->except('email'));
        $vendor->avatar_type = 'storage';
        if ($request->image) {
            $vendor->avatar_location = $request->image->store('/avatars', 'public');
        } else {
            // No image being passed
            // If there is no existing image
            if (!strlen(auth()->user()->avatar_location)) {
                //throw new GeneralException('You must supply a profile image.');
            }
        }
        $vendor->save();

        return redirect()->route('admin.getusers.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vendor = User::findOrFail($id);

        return view('backend.siteadminusers.show', compact('vendor'));
    }


    /**
     * Remove Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $vendor = User::findOrFail($id);
        $vendor->delete();
        return redirect()->route('admin.getusers.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Client at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {

        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

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
        $vendor = User::onlyTrashed()->findOrFail($id);
        $vendor->restore();

        return redirect()->route('admin.getusers.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {

        $vendor = User::onlyTrashed()->findOrFail($id);
        $vendor->forceDelete();

        return redirect()->route('admin.getusers.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
