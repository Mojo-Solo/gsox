<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;
use App\Helpers\Auth\Auth;
use DB;
class UsersController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('user_access')) {
            return abort(401);
        }

        $users = User::orderBy('created_at','asc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('user_create')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('title', 'id');

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersRequest $request)
    {
        if (!Gate::allows('user_create')) {
            return abort(401);
        }
        $user = User::create($request->all());
        $user->role()->sync(array_filter((array)$request->input('role')));


        return redirect()->route('admin.users.index');
    }


    /**
     * Show the form for editing User.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('user_edit')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('title', 'id');

        $user = User::findOrFail($id);
        $vendors = Vendor::all();
        return view('admin.users.edit', compact('user', 'roles','vendors'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersRequest $request, $id)
    {
        if (!Gate::allows('user_edit')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        $user->update($request->all());
        $user->role()->sync(array_filter((array)$request->input('role')));


        return redirect()->route('admin.users.index');
    }


    /**
     * Display User.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('user_view')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('title', 'id');
        $courses = \App\Models\Course::whereHas('Supervisors',
            function ($query) use ($id) {
                $query->where('id', $id);
            })->get();

        $user = User::findOrFail($id);

        return view('admin.users.show', compact('user', 'courses'));
    }


    /**
     * Remove User from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('user_delete')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index');
    }

    /**
     * Delete all selected User at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('user_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

    public function switchLogin()
    {
        if(auth()->guard('vendor')->check() && auth()->guard('vendor')->user()->is_student) {
            $user=User::where('email',auth()->guard('vendor')->user()->email)->first();
            if($user) {
                \Illuminate\Support\Facades\Auth::guard('vendor')->logout();
                // Login user
                \Illuminate\Support\Facades\Auth::guard('web')->loginUsingId($user->id);
                session(['student_user_id' => $user->id]);
                // Redirect to frontend
                return redirect()->route(home_route());
            }
            // Redirect to frontend
            return redirect()->route(home_route())->with('error','Student not found');
        }elseif(auth()->user() && auth()->user()->hasRole('student')) {
            $vendor=Vendor::where('email',auth()->user()->email)->first();
            if($vendor) {
                auth()->logout();
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                \Illuminate\Support\Facades\Auth::guard('vendor')->loginUsingId($vendor->id);
                session(['student_user_id' => '']);
            }
        }

        // Redirect to frontend
        return redirect()->route(home_route());
    }

}
