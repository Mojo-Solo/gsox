<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Client;
use App\Models\Tag;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if(session()->has('display_type')){
            if(session('display_type') == 'rtl'){
                $path = 'frontend-rtl';
            }else{
                $path = 'frontend';
            }
        }else if(config('app.display_type') == 'rtl'){
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }


    public function getByClient(Request $request)
    {
        $popular_tags = Tag::has('blogs', '>', 4)->get();

        $Client = Client::where('slug', '=', str_slug($request->Client))->first();
        $Clients = Client::has('blogs')->where('status', '=', 1)->paginate(10);
        if ($Client != "") {
            $blogs = $Client->blogs()->paginate(6);
            return view($this->path.'.blogs.index', compact('Client', 'blogs', 'popular_tags', 'Clients'));
        }
        return abort(404);
    }

    public function getIndex(Request $request)
    {
        $popular_tags = Tag::has('blogs', '>', 4)->get();
        $Clients = Client::has('blogs')->where('status', '=', 1)
            ->take(10)->get();

        if ($request->slug != "") {
            $blog_id = array_last(explode('-', $request->slug));
            $blog = Blog::findOrFail($blog_id);
            // get previous user id
            $previous_id = Blog::where('id', '<', $blog_id)->max('id');
            $previous = Blog::find($previous_id);

            // get next user id
            $next_id = Blog::where('id', '>', $blog_id)->min('id');
            $next = Blog::find($next_id);

            $related_news = $blog->Client->blogs()->where('id','!=',$blog->id)->take(2)->get();

            return view($this->path.'.blogs.blog-single', compact('blog','previous','next','popular_tags','Clients','related_news'));
        }


        $blogs = Blog::has('Client')->OrderBy('created_at','desc')->paginate(6);
        return view($this->path.'.blogs.index',
            compact( 'blogs', 'Clients', 'popular_tags'));
    }

    public function getByTag(Request $request)
    {
        $popular_tags = Tag::has('blogs', '>', 4)->get();
        $tag = Tag::where('slug', '=', str_slug($request->tag))->first();
        $Clients = Client::has('blogs')->where('status', '=', 1)->paginate(10);
        if ($tag != "") {
            $blogs = $tag->blogs()->paginate(6);
            return view($this->path.'.blogs.index', compact('tag', 'blogs', 'Clients', 'popular_tags'));
        }
        return abort(404);
    }

    public function storeComment(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required|min:3',
        ]);
        $blog = Blog::findOrfail($request->id);
        $blogcooment = new BlogComment($request->all());
        $blogcooment->name = auth()->user()->full_name;
        $blogcooment->email = auth()->user()->email;
        $blogcooment->comment = $request->comment;
        $blogcooment->blog_id = $blog->id;
        $blogcooment->user_id = auth()->user()->id;
        $blogcooment->save();
        return back();
    }

    public function deleteComment($id){
        $comment = BlogComment::findOrFail($id);
        if(auth()->user()->id == $comment->user_id){
            $comment->delete();
            return back();
        }
        return abort(419);
    }

}
