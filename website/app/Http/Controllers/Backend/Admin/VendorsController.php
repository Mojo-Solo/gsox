<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreVendorsRequest;
use App\Http\Requests\Admin\UpdateVendorsRequest;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Models\Category;

class VendorsController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (request('show_deleted') == 1) {

            $users = User::role('vendor')->onlyTrashed()->get();
        } else {
            $users = User::role('vendor')->get();
        }

        return view('backend.vendors.index', compact('users'));
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
        $vendors = "";


        if (request('show_deleted') == 1) {
            $vendors = User::role('vendor')->onlyTrashed()->orderBy('created_at', 'desc')->get();
        } else {
            $vendors = User::role('vendor')->orderBy('created_at', 'desc')->get();
        }

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }


        return DataTables::of($vendors)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.vendors', 'label' => 'vendor', 'value' => $q->id]);
                }
//                if ($has_view) {
//                    $view = view('backend.datatable.action-view')
//                        ->with(['route' => route('admin.vendors.show', ['vendor' => $q->id])])->render();
//                }

                //if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.vendors.edit', ['vendor' => $q->id])])
                        ->render();
                    $view .= $edit;
                //}

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.vendors.destroy', ['vendor' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

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
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $all_clients = Category::get();
        $vendor_clients = array();
        return view('backend.vendors.create', compact('all_clients','vendor_clients'));
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreVendorsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVendorsRequest $request)
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

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vendor = User::findOrFail($id);
        $all_clients = Category::get();
        $vendor_clients = $vendor->vendor_clients()->pluck('client_id')->toArray();
        return view('backend.vendors.edit', compact('vendor', 'all_clients', 'vendor_clients'));
    }

    /**
     * Update Category in storage.
     *
     * @param  \App\Http\Requests\UpdateVendorsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVendorsRequest $request, $id)
    {
        //$request = $this->saveFiles($request);

        $vendor = User::findOrFail($id);
        $vendor->update($request->except('email', 'vendor_clients'));
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
        if($request->input('vendor_clients')){
          $client_check = $vendor->vendor_clients()->forceDelete();
          foreach($request->input('vendor_clients') as $vendor_client){
                $vendor->vendor_clients()->create(['client_id' => $vendor_client]);
          } 
        }
        $vendor->save();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vendor = User::findOrFail($id);

        return view('backend.vendors.show', compact('vendor'));
    }


    /**
     * Remove Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $vendor = User::findOrFail($id);
        $vendor->delete();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Category at once.
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
     * Restore Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $vendor = User::onlyTrashed()->findOrFail($id);
        $vendor->restore();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {

        $vendor = User::onlyTrashed()->findOrFail($id);
        $vendor->forceDelete();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
