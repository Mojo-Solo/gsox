<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use DB;

class InvoiceController extends Controller
{
    /**
     * Get invoice list of current user
     *
     * @param Request $request
     */
    public function getIndex(){
        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->students();
            $invoices = DB::table('invoices')
                    ->join('orders', 'orders.id', '=', 'invoices.order_id')
                    ->whereIn('invoices.user_id',$user_ids)
                    ->select('invoices.*')
                    ->get();
        } else {
            $invoices = auth()->user()->invoices()->whereHas('order')->get();
        }
        return view('backend.invoices.index',compact('invoices'));
    }


    /**
     * Download order invoice
     *
     * @param Request $request
     */
    public function getInvoice(Request $request)
    {
        if (auth()->check()) {
            $order = Order::findOrFail($request->order);
            if (auth()->user()->isAdmin() || ($order->user_id == auth()->user()->id)) {
                $file = public_path() . "/storage/invoices/" . $order->invoice->url;
                return Response::download($file);
            }
        }
        return abort(404);
    }

}
