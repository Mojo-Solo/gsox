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
use App\Models\Order;
use App\Models\Bundle;
use App\Models\Course;
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

            if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) 
            {
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
                // dd(auth()->user()->students());
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

            if((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) 
            {
                $vendors=Vendor::where('created_by',auth()->user()->id)->get()->pluck('company_name', 'id')->prepend('Please select', '');
            }
            elseif(auth()->user()->hasRole('supervisor'))
            {
                $ids=array();

                foreach(Vendor::orderBy('created_at', 'desc')->get() as $vendor) 
                {
                    if(in_array(auth()->user()->client_id, explode(",", $vendor->clients))) 
                    {
                        array_push($ids, $vendor->id);
                    }
                }

                $vendors=Vendor::whereIn('id',$ids)->pluck('company_name', 'id')->prepend('Please select', '');
            } 
            else 
            {
                if ((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name == "student")) 
                {
                    $vendors = Vendor::where('id',auth()->user()->vendor_id)->get()->pluck('company_name', 'id')->prepend('Please select', '');
                    $clients =  Vendor::where('id',auth()->user()->vendor_id)
                    ->pluck('clients'); 
                }
                elseif(auth()->guard('vendor')->check())
                {

                    $vendors = Vendor::where('id',auth()->user()->id)
                    ->pluck('company_name','id')->prepend('Please select', '');

                    $clients =  Vendor::where('id',auth()->user()->id)
                    ->pluck('clients'); 
                }
                else
                {
                     $vendors = Vendor::where('id',auth()->user()->vendor_id)->get()->pluck('company_name', 'id')->prepend('Please select', '');
                    $clients =  Vendor::where('id',auth()->user()->vendor_id)
                    ->pluck('clients'); 
                }
      
                $ids = explode(",", $clients[0]);
                $courses = Course::whereIn('client_id',$clients)->get();

            }

            return view('backend.students.create',compact('vendors','courses'));
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

            $courses = array();

            if (isset($request->courses)) {
              
                for ($i=0; $i < count($request->courses); $i++) { 
                    $courses[$i]['user_id'] = $user->id;
                    $courses[$i]['course_id'] = $request->courses[$i];
                }
                
                \DB::table('course_user')->insert($courses);

                $this->offlinePayment($user,$request->courses);
            }
        }
        else{
            abort(404);
        }

        return redirect()->route('admin.students.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

     public function offlinePayment($user,$courses)
    {
        //Making Order
        $order = $this->makeOrder($user,$courses);
        $order->payment_type = 3;
        $order->status = 0;
        $order->save();

        $order = Order::findOrfail($order->id);
        $order->status = 1;
        $order->save();

        //Generating Invoice
        generateInvoice($order);

        foreach ($order->items as $orderItem) {
            //Bundle Entries
            if($orderItem->item_type == Bundle::class){
               foreach ($orderItem->item->courses as $course){
                   $course->students()->attach($order->user_id);
               }
            }
            $orderItem->item->students()->attach($order->user_id);
        }
    }

     private function makeOrder($user,$courses)
    {
        $courses = Course::whereIn('id',$courses)->get();
        $totalSum = $courses->sum('price');

        $order = new Order();
        $order->user_id = $user->id;
        $order->reference_no = str_random(8);
        $order->amount = $totalSum;
        $order->status = 0;
        $order->coupon_id = 0;
        $order->payment_type = 3;
        $order->save();
        //Getting and Adding items
        foreach ($courses as $cartItem) {
            $type = 'App\Models\Course';
            $order->items()->create([
                'item_id' => $cartItem->id,
                'item_type' => $type,
                'price' => $cartItem->price,
                'invoice_status' => 0
            ]);
        }

        return $order;
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
