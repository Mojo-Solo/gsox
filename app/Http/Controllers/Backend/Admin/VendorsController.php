<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreVendorsRequest;
use App\Http\Requests\Admin\UpdateVendorsRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Models\Client;
use Hash;
class VendorsController extends Controller
{
    use FileUploadTrait;

    public function __construct() {
        if(!empty(auth()->user()) && !auth()->user()->isAdmin() && !auth()->user()->hasAnyPermission(['vendor_management_access','vendor_management_create','vendor_management_edit','vendor_management_view','vendor_management_delete'])) {
            abort(404);
        }
    }

    /**
     * Display a listing of Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.vendors.index');
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
        $vendors = array();


        if (request('show_deleted') == 1) {
            $vendors = Vendor::onlyTrashed()->orderBy('created_at', 'desc')->get();
        } else {
            if(auth()->user()->roles[0]->name=="manager") {
                $vendors = Vendor::where('created_by',auth()->user()->id)->orderBy('created_at', 'desc')->get();
            } elseif(auth()->user()->hasRole('supervisor')) {
                foreach(Vendor::orderBy('created_at', 'desc')->get() as $vendor) {
                    if(in_array(auth()->user()->client_id, explode(",", $vendor->clients))) {
                        array_push($vendors, $vendor);
                    }
                }
            } else {
                $vendors = Vendor::orderBy('created_at', 'desc')->get();
            }
        }
        if (auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_access','vendor_management_create','vendor_management_edit','vendor_management_view','vendor_management_delete'])) {
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

                if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_edit'])) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.vendors.edit', ['vendor' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete'])) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.vendors.destroy', ['vendor' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

               // $view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['vendor_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;

            })->editColumn('clients', function ($q) {
                if (empty($q->clients)) {
                    return 'N/A';
                }else{
                    $client_ids=explode(",", $q->clients);
                    $clients=Client::whereIn('id',$client_ids)->pluck('name')->toArray();
                    return implode(", ", $clients);
                }
                return '';
            })->rawColumns(['actions', 'image'])->make();
    }

    /**
     * Show the form for creating new Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_create']))
            return view('backend.vendors.create');
        else
            abort(404);
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param  \App\Http\Requests\StoreVendorsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVendorsRequest $request)
    {
//        $request = $this->saveFiles($request);
        $created_by='';
        if(auth()->user()->roles[0]->name="manager") {
            $created_by=auth()->user()->id;
        }
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_create']) || auth()->user()->hasRole('supervisor')) {
            if(auth()->user()->hasRole('supervisor')) {
                $clients=auth()->user()->client_id;
            } else {
                $clients=implode(",", $request->clients);
            }
            if($request->invoicing==null) {
                $data=[
                'name' => $request->name,
                'email' => $request->email,
                'is_student' => ($request->is_student)?1:0,
                'company_name' => $request->company_name,
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'country_id' => $request->country_id,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'invoicing' => 0,
                'confirmed' => 1,
                'created_by' => $created_by,
                'clients' => $clients,
            ];
        } else {
            $data=[
                'name' => $request->name,
                'email' => $request->email,
                'is_student' => ($request->is_student)?1:0,
                'company_name' => $request->company_name,
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'country_id' => $request->country_id,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'invoicing' => 1,
                'confirmed' => 1,
                'created_by' => $created_by,
                'clients' => $clients,
            ];
        }
            $vendor = Vendor::create($data);
        }
        else{
            abort(404);
        }

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_edit']))
            $vendor = Vendor::findOrFail($id);
        else
            abort(404);
        return view('backend.vendors.edit', compact('vendor'));
    }

    /**
     * Update Client in storage.
     *
     * @param  \App\Http\Requests\UpdateVendorsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVendorsRequest $request, $id)
    {
        //$request = $this->saveFiles($request);
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_edit']))
            $vendor = Vendor::findOrFail($id);
        else
            abort(404);
        $vendor->name = $request->name;
        $vendor->is_student = ($request->is_student)?1:0;
        $vendor->confirmed = ($request->confirmed)?1:0;
        $vendor->company_name = $request->company_name;
        $vendor->contact_name = $request->contact_name;
        $vendor->contact_email = $request->contact_email;
        $vendor->phone_number = $request->phone_number;
        $vendor->country_id = $request->country_id;
        $vendor->city = $request->city;
        $vendor->state = $request->state;
        $vendor->zip = $request->zip;
        $vendor->address1 = $request->address1;
        $vendor->address2 = $request->address2;
        if(!empty($request->password))
            $vendor->password=Hash::make($request->password);
        if($request->invoicing==null) {
            $vendor->invoicing=0;
            $vendor->clients=(auth()->user()->hasRole('supervisor'))?auth()->user()->client_id:implode(",", $request->clients);
        } else {
            $vendor->clients=(auth()->user()->hasRole('supervisor'))?auth()->user()->client_id:implode(",", $request->clients);
            $vendor->invoicing=1;
        }
        $vendor->save();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('backend.vendors.show', compact('vendor'));
    }


    /**
     * Remove Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['vendor_management_delete']))
            $vendor = Vendor::findOrFail($id);
        else
            abort(404);
        $vendor->delete();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Client at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {

        if ($request->input('ids')) {
            $entries = Vendor::whereIn('id', $request->input('ids'))->get();

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
        $vendor = Vendor::onlyTrashed()->findOrFail($id);
        $vendor->restore();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {

        $vendor = Vendor::onlyTrashed()->findOrFail($id);
        $vendor->forceDelete();

        return redirect()->route('admin.vendors.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
