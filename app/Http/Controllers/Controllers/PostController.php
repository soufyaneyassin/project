<?php

namespace App\Http\Controllers\Controllers;

use App\Controllers\Post;
use App\Events\NewPost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Controllers\PostStoreRequest;
use App\Jobs\SyncMedia;
use App\Mail\ReviewPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $posts = Post::all();

        return view('post.index', compact('posts'));
    }

    /**
     * @param \App\Http\Requests\Controllers\PostStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostStoreRequest $request)
    {
        $post = Post::create($request->validated());

        Mail::to($post->author->email)->send(new ReviewPost($post));

        SyncMedia::dispatch($post);

        event(new NewPost($post));

        $request->session()->flash('post.title', $post->title);

        return redirect()->route('post.index');
    }
}
