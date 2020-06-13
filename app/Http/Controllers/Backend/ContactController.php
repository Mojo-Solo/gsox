<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;


class ContactController extends Controller
{
    /**
     * Display a listing of Client.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contact::all();

        return view('backend.contacts.index', compact('contacts'));
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $contacts = "";
        $contacts = Contact::orderBy('created_at', 'desc')->get();


        return DataTables::of($contacts)
            ->addIndexColumn()
            ->editColumn('created_at', function ($q) {
               return $q->created_at->format('d M, Y | H:i A');
            })
            ->editColumn('number', function ($q) {
                if($q->number == ""){
                    return "N/A";
                }else{
                    return $q->number;
                }
            })
            ->make();
    }
    public function destroy(Request $request,$id) {
        $contact=Contact::findOrFail($id);
        Contact::destroy($id);
        return redirect()->back()->withFlashSuccess(__('SuccessFull Deleted'));
    }
}
