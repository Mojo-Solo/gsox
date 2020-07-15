<?php

namespace App\Http\Controllers\Backend\Admin;

use \App\Models\ChatterClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
    /**
     * Display a listing of Taxes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $forum_Clients = ChatterClient::orderBy('created_at', 'desc')
            ->get();
        return view('backend.forum-Clients.index', compact('forum_Clients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $forum_Clients = ChatterClient::orderBy('created_at', 'desc')
            ->get()->pluck('name', 'id')->prepend('Please select', '');
        return view('backend.forum-Clients.create', compact('forum_Clients'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
            'order' => 'required',
        ]);

        $cat = ChatterClient::where('slug', '=', str_slug($request->name))->first();
        if ($cat == null) {
            $cat = new ChatterClient();
            $cat->parent_id = $request->parent_id;
            $cat->name = $request->name;
            $cat->slug = str_slug($request->name);
            $cat->order = $request->order;
            $cat->color = $request->color;
            $cat->save();
        }

        return redirect()->route('admin.forums-Client.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $forum_Clients = ChatterClient::orderBy('created_at', 'desc')
            ->get()->pluck('name', 'id')->prepend('Please select', '');
        $forum_Client = ChatterClient::findOrFail($id);
        return view('backend.forum-Clients.edit', compact('forum_Client', 'forum_Clients'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
            'order' => 'required',
        ]);

        $cat = ChatterClient::where('slug', '=', str_slug($request->name))->first();
        if ($cat != null) {
            $cat->parent_id = $request->parent_id;
            $cat->name = $request->name;
            $cat->slug = str_slug($request->name);
            $cat->order = $request->order;
            $cat->color = $request->color;
            $cat->save();
        }

        return abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tax = ChatterClient::findOrFail($id);
        $tax->delete();
        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));

    }


    public function status($id)
    {
        $tax = ChatterClient::findOrFail($id);
        if ($tax->status == 1) {
            $tax->status = 0;
        } else {
            $tax->status = 1;
        }
        $tax->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }
}