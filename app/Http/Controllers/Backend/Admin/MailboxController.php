<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Hash;
class MailboxController extends Controller
{

    public function __construct() {
        if(!auth()->user()->isAdmin()) {
            abort(404);
        }
    }

    /**
     * Display a listing of mails.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.mailbox.index');
    }

    
}
