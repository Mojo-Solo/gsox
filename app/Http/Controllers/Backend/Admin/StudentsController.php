<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreVendorsRequest;
use App\Http\Requests\Admin\UpdateVendorsRequest;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Models\Client;
use App\Models\Vendor;
use Hash;
use DB;
class StudentsController extends Controller
{
    use FileUploadTrait;

    public function __construct() {
        if(!empty(auth()->user()) && !auth()->user()->isAdmin() && !auth()->user()->hasAnyPermission(['student_management_access','student_management_create','student_management_edit','student_management_view','student_management_delete'])) {
            abort(404);
        }
    }

    /**
     * Display a listing of Students.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager") || auth()->user()->hasRole('supervisor')) {
            if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
                $vendors=Vendor::where('created_by',auth()->user()->id)->pluck('id')->toArray();
                $user_ids = \DB::table('users')
                            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('roles.name', '=', 'student')
                            ->whereIn('users.vendor_id', $vendors)
                            ->pluck('users.id')
                            ->toArray();
                $students=User::whereIn('id',$user_ids)->get();
            } else {
                $user_ids=auth()->user()->students();
                $students=User::whereIn('id',$user_ids)->get();
            }
        } else {
        $students = DB::table('users')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', '=', 'student')
            ->where('users.vendor_id', '=', auth()->user()->vendor_id)
            ->select('users.*')
            ->get();
        }
        return view('backend.students.index', compact('students'));
    }

    /**
     * Show the form for creating new Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_create']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager") || auth()->user()->hasRole('supervisor')) {
            if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
                $vendors=Vendor::where('created_by',auth()->user()->id)->get()->pluck('company_name', 'id')->prepend('Please select', '');
            }elseif(auth()->user()->hasRole('supervisor')){
                $ids=array();
                foreach(Vendor::orderBy('created_at', 'desc')->get() as $vendor) {
                    if(in_array(auth()->user()->client_id, explode(",", $vendor->clients))) {
                        array_push($ids, $vendor->id);
                    }
                }
                $vendors=Vendor::whereIn('id',$ids)->pluck('company_name', 'id')->prepend('Please select', '');
            } else {
                $vendors=Vendor::get()->pluck('company_name', 'id')->prepend('Please select', '');
            }
            return view('backend.students.create',compact('vendors'));
        }
        else {
            abort(404);
        }
    }

    /**
     * Store a newly created Client in storage.
     *
     * @param  \App\Http\Requests\StoreVendorsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
        ]);
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_create']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
            $user = User::create($request->all());
            $user->vendor_id=$request->vendor_id;
            $user->save();
            $student_role_id=DB::table('roles')->where('name','student')->value('id');
            $role=[
                'role_id' => $student_role_id,
                'model_type' => 'App\Models\Auth\User',
                'model_id' => $user->id,
            ];
            DB::table('model_has_roles')->insert($role);
        }
        else{
            abort(404);
        }

        return redirect()->route('admin.students.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_edit']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager"))
            $user = User::findOrFail($id);
        else
            abort(404);
        $role_id=DB::table('model_has_roles')->where('model_id',$user->id)->value('role_id');
        if(!$role_id)
            abort(404);
        $role=Role::findOrFail($role_id);
        if($role->name!="student")
            abort(404);
        $roles = Role::get()->pluck('title', 'id');
        if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
                $vendors=Vendor::where('created_by',auth()->user()->id)->get()->pluck('company_name', 'id')->prepend('Please select', '');
            }elseif(auth()->user()->hasRole('supervisor')){
                $ids=array();
                foreach(Vendor::orderBy('created_at', 'desc')->get() as $vendor) {
                    if(in_array(auth()->user()->client_id, explode(",", $vendor->clients))) {
                        array_push($ids, $vendor->id);
                    }
                }
                $vendors=Vendor::whereIn('id',$ids)->pluck('company_name', 'id')->prepend('Please select', '');
            } else {
                $vendors=Vendor::get()->pluck('company_name', 'id')->prepend('Please select', '');
            }
        return view('backend.students.edit', compact('user','roles','vendors'));
    }

    /**
     * Update Client in storage.
     *
     * @param  \App\Http\Requests\UpdateVendorsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //$request = $this->saveFiles($request);
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_edit']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager"))
            $user = User::findOrFail($id);
        else
            abort(404);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->vendor_id = $request->vendor_id;
        $user->active = $request->status;
        if(!empty($request->password))
            $user->password=Hash::make($request->password);
        $user->save();

        return redirect()->route('admin.students.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Client.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_view']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager"))
            $user = User::findOrFail($id);
        else
            abort(404);
        $role_id=DB::table('model_has_roles')->where('model_id',$user->id)->value('role_id');
        if(!$role_id)
            abort(404);
        $role=Role::findOrFail($role_id);
        if($role->name!="student")
            abort(404);
        return view('backend.students.show', compact('user'));
    }


    /**
     * Remove Client from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(auth()->user()->isAdmin() || auth()->user()->hasAnyPermission(['student_management_delete']) || auth()->guard('vendor')->check() || (isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager"))
            $user = User::findOrFail($id);
        else
            abort(404);
        $user->forceDelete();

        return redirect()->route('admin.students.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
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
