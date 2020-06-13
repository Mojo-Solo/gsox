<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreSupervisorsRequest;
use App\Http\Requests\Admin\UpdateSupervisorsRequest;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Models\Client;

class SupervisorsController extends Controller
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

            $users = User::role('Supervisor')->onlyTrashed()->get();
        } else {
            $users = User::role('Supervisor')->get();
        }

        return view('backend.Supervisors.index', compact('users'));
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
        $Supervisors = "";


        if (request('show_deleted') == 1) {

            $Supervisors = User::role('Supervisor')->onlyTrashed()->orderBy('created_at', 'desc')->get();
        } else {
            $Supervisors = User::role('Supervisor')->orderBy('created_at', 'desc')->get();
        }

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }

        return DataTables::of($Supervisors)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.Supervisors', 'label' => 'Supervisor', 'value' => $q->id]);
                }
//                if ($has_view) {
//                    $view = view('backend.datatable.action-view')
//                        ->with(['route' => route('admin.Supervisors.show', ['Supervisor' => $q->id])])->render();
//                }

                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.Supervisors.edit', ['Supervisor' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.Supervisors.destroy', ['Supervisor' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                $view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['Supervisor_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;

            })
            ->editColumn('status', function ($q) {
                return ($q->active == 1) ? "Enabled" : "Disabled";
            })
            ->editColumn('client_id', function ($q) {
                $client_name = '';
                if($q->client_id){
                    $client = Client::findorfail($q->client_id);
                    $client_name = $client->name;
                }
                return $client_name;
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
        $clients = Client::get()->pluck('name', 'id')->prepend('Please select', '');;
        return view('backend.Supervisors.create', compact('clients'));
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param  \App\Http\Requests\StoreSupervisorsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSupervisorsRequest $request)
    {
//        $request = $this->saveFiles($request);

        $user = User::create($request->all());
        $user->confirmed = 1;
        $user->avatar_type = 'storage';
        if ($request->image) {
            $user->avatar_location = $request->image->store('/avatars', 'public');
        }
        $user->save();

        $user->assignRole('Supervisor');

        return redirect()->route('admin.Supervisors.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Supervisor = User::findOrFail($id);
        $clients = Client::get()->pluck('name', 'id')->prepend('Please select', '');
        return view('backend.Supervisors.edit', compact('Supervisor', 'clients'));
    }

    /**
     * Update Client in storage.
     *
     * @param  \App\Http\Requests\UpdateSupervisorsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSupervisorsRequest $request, $id)
    {
        //$request = $this->saveFiles($request);

        $Supervisor = User::findOrFail($id);
        $Supervisor->update($request->except('email'));
        $Supervisor->avatar_type = 'storage';
        if ($request->image) {
            $Supervisor->avatar_location = $request->image->store('/avatars', 'public');
        } else {
            // No image being passed
            // If there is no existing image
            if (!strlen(auth()->user()->avatar_location)) {
                //throw new GeneralException('You must supply a profile image.');
            }
        }
        $Supervisor->save();

        return redirect()->route('admin.Supervisors.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Supervisor = User::findOrFail($id);

        return view('backend.Supervisors.show', compact('Supervisor'));
    }


    /**
     * Remove Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $Supervisor = User::findOrFail($id);
        $Supervisor->delete();

        return redirect()->route('admin.Supervisors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
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
        $Supervisor = User::onlyTrashed()->findOrFail($id);
        $Supervisor->restore();

        return redirect()->route('admin.Supervisors.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {

        $Supervisor = User::onlyTrashed()->findOrFail($id);
        $Supervisor->forceDelete();

        return redirect()->route('admin.Supervisors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
