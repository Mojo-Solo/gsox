<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Bundle;
use App\Models\Course;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Response;


class OrderController extends Controller
{

    /**
     * Display a listing of Orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.orders.index');
    }

    /**
     * Display a listing of Orders via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        if (request('offline_requests') == 1) {

            if(auth()->guard('vendor')->check() || auth()->user()->hasRole('supervisor')) {
                $user_ids=auth()->user()->students();
                $orders = Order::where('payment_type', '=', 3)->whereIn('user_id',$user_ids)->orderBy('updated_at', 'desc')->get();
            } elseif((isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager")) {
                $vendors=Vendor::where('created_by',auth()->user()->id)->pluck('id')->toArray();
                $user_ids = \DB::table('users')
                            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('roles.name', '=', 'student')
                            ->whereIn('users.vendor_id', $vendors)
                            ->pluck('users.id')
                            ->toArray();
                $orders = Order::where('payment_type', '=', 3)->whereIn('user_id',$user_ids)->orderBy('updated_at', 'desc')->get();
            } else {
                if(auth()->guard('vendor')->check()) {
                    $user_ids=auth()->user()->students();
                    $orders = Order::where('payment_type', '=', 3)->whereIn('user_id',$user_ids)->orderBy('updated_at', 'desc')->get();
                } else {
                    $orders = Order::where('payment_type', '=', 3)->orderBy('updated_at', 'desc')->get();
                }
            }
        } else {

            if(auth()->guard('vendor')->check() || auth()->user()->hasRole('supervisor') || (isset(auth()->user()->roles[0]) && auth()->user()->roles->pluck('name')->contains('vendor'))) {

                if ((isset(auth()->user()->roles[0]) && auth()->user()->roles->pluck('name')->contains('student'))) 
                {
                    $user_ids = Vendor::where('id',auth()->user()->vendor_id)->first();
                    $user_ids = $user_ids->CustomFetcherstudents(auth()->user()->vendor_id);
                }
                elseif((isset(auth()->user()->roles[0]) && auth()->user()->roles->pluck('name')->contains('vendor')))
                {
                    $user_ids = Vendor::where('id',auth()->user()->vendor_id)->first();
                    $user_ids = $user_ids->CustomFetcherstudents(auth()->user()->vendor_id);
                }
                else
                {
                    $user_ids=auth()->user()->students(); 
                }

                $orders = Order::whereIn('user_id',$user_ids)->orderBy('updated_at', 'desc')->get();
            }
            elseif(isset(auth()->user()->roles[0]) && auth()->user()->roles[0]->name=="manager") {
                $vendors=Vendor::where('created_by',auth()->user()->id)->pluck('id')->toArray();
                $user_ids = \DB::table('users')
                            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('roles.name', '=', 'student')
                            ->whereIn('users.vendor_id', $vendors)
                            ->pluck('users.id')
                            ->toArray();
                $orders = Order::where('payment_type', '=', 3)->whereIn('user_id',$user_ids)->orderBy('updated_at', 'desc')->get();
            } 
            else {
                $orders = Order::orderBy('updated_at', 'desc')->get();
            }
        }

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($request) {
                $view = "";

                $view = view('backend.datatable.action-view')
                    ->with(['route' => route('admin.orders.show', ['order' => $q->id])])->render();

                if ($q->status == 0) {
                    $complete_order = view('backend.datatable.action-complete-order')
                        ->with(['route' => route('admin.orders.complete', ['order' => $q->id])])
                        ->render();
                    $view .= $complete_order;
                }
                if(!auth()->guard('vendor')->check()) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.orders.destroy', ['order' => $q->id])])
                        ->render();

                    $view .= $delete;
                }

                return $view;

            })
            ->addColumn('items', function ($q) {
                $items = "";
                foreach ($q->items as $key => $item) {
                    if($item->item != null){
                        $key++;
                        $items .= $key . '. ' . $item->item->title . "<br>";
                    }

                }
                return $items;
            })
            ->addColumn('user_email', function ($q) {
                return (isset($q->user->email))?$q->user->email:'N/A';
            })
            ->addColumn('date', function ($q) {
                return $q->updated_at->diffforhumans();
            })
            ->addColumn('payment', function ($q) {
                if ($q->status == 0) {
                    $payment_status = trans('labels.backend.orders.fields.payment_status.pending');
                } elseif ($q->status == 1) {
                    $payment_status = trans('labels.backend.orders.fields.payment_status.completed');
                } else {
                    $payment_status = trans('labels.backend.orders.fields.payment_status.failed');
                }
                return $payment_status;
            })
            ->editColumn('price', function ($q) {
                return '$' . floatval($q->price);
            })
            ->rawColumns(['items', 'actions'])
            ->make();
    }

    /**
     * Complete Order manually once payment received.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function complete(Request $request)
    {
        $order = Order::findOrfail($request->order);
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
        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    /**
     * Show Order from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.orders.show', compact('order'));
    }

    /**
     * Remove Order from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(auth()->guard('vendor')->check() || auth()->user()->hasRole('supervisor'))
            abort(404);
        $order = Order::findOrFail($id);
        $order->items()->delete();
        $order->delete();
        return redirect()->route('admin.orders.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Orders at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if(auth()->guard('vendor')->check() || auth()->user()->hasRole('supervisor'))
            abort(404);
        if (!Gate::allows('course_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Order::whereIn('id', $request->input('ids'))->get();
            foreach ($entries as $entry) {
                if ($entry->status = 1) {
                    foreach ($entry->items as $item) {
                        $item->course->students()->detach($entry->user_id);
                    }
                    $entry->items()->delete();
                    $entry->delete();
                }
            }
        }
    }


}
