<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CertificateController extends Controller
{
    public function __construct()
    {

        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    /**
     * Get certificates lost for purchased courses.
     */
    public function getCertificates()
    {
        if(auth()->guard('vendor')->check() || auth()->user()->hasRole('supervisor')) {
            $user_ids=auth()->user()->students();
            $certificates=Certificate::whereIn('user_id',$user_ids)->where('status',1)->get();
        } else {
            $certificates = auth()->user()->certificates;
        }
        return view('backend.certificates.index', compact('certificates'));
    }


    /**
     * Generate certificate for completed course
     */
    public function generateCertificate(Request $request)
    {
        $client_ids=Vendor::findOrFail(auth()->user()->vendor_id)->clientsById(auth()->user()->vendor_id);
        $client=Client::findOrFail($client_ids[0])->name;
        $client_object=Client::findOrFail($client_ids[0]);

        $course = Course::whereHas('students', function ($query) {
            $query->where('id', \Auth::id());
        })->where('id', '=', $request->course_id)->first();
       $OrderItem=OrderItem::where('item_type','App\Models\Course')->where('item_id',$request->course_id)->first();
       $order=Order::findOrFail($OrderItem->order_id);
       $order->status=1;
       $order->save();
        if (($course != null) && ($course->progress() == 100)) {
            $certificate = Certificate::firstOrCreate([
                'user_id' => auth()->user()->id,
                'course_id' => $request->course_id
            ]);
            $certuuid=uniqid();
            $data = [
                'name' => auth()->user()->name,
                'course_name' => $course->title,
                'date' => Carbon::now()->format('d M, Y'),
            ];
            $certificate_name = 'Certificate-' . $course->id . '-' . auth()->user()->id . '.pdf';
            $certificate->name = auth()->user()->id;
            $certificate->url = $certificate_name;
            $certificate->uuid = $certuuid;
            $certificate->save();

            $pdf = \PDF::loadView('certificate.index', compact('data','client_object','certuuid'))->setPaper('', 'landscape');

            $pdf->save(public_path('storage/certificates/' . $certificate_name));

            return back()->withFlashSuccess(trans('alerts.frontend.course.completed'));
        }
        return abort(404);
    }

    /**
     * Download certificate for completed course
     */
    public function download(Request $request)
    {
        $certificate = Certificate::findOrFail($request->certificate_id);
        if($certificate != null){
            $file = public_path() . "/storage/certificates/" . $certificate->url;
            return Response::download($file);
        }
        return back()->withFlashDanger('No Certificate found');


    }


    /**
     * Get Verify Certificate form
     */
    public function getVerificationForm()
    {
        return view($this->path.'.certificate-verification');
    }


    /**
     * Verify Certificate
     */
    public function verifyCertificate(Request $request)
    {
        $this->validate($request, [
            'uid' => 'required'
        ]);

        $certificates = Certificate::where('uuid', $request->uid)->get();
        $data['certificates'] = $certificates;
        $data['name'] = $request->name;
        $data['date'] = $request->date;
        $data['uid'] = $request->uid;
        session()->forget('certificates');
        return back()->with(['data' => $data]);

    }
}
