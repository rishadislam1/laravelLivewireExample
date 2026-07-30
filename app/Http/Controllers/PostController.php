<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function search($term){
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }
    public function showCreatePost(){

        return view('create-post');
    }

    public function createPost(Request $request){
        $incommingRequest = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incommingRequest['title']);
        $incomingFields['body'] = strip_tags($incommingRequest['body']);
        $incomingFields['user_id']=auth()->id();

        $newPost = Post::create($incomingFields);

        return redirect("/post/{$newPost->id}")->with('success', 'You have been created Post Successfully!');
    }

    public function viewSinglePost(Post $id){
        if($id->user_id === auth()->user()->id){

        }
        $id['body'] = strip_tags( Str::markdown($id->body),'<p><a><h1><h2><h3><h4><h4><h5><h6><ul></ul><li></li><em></em><br/>');

        return view('single-post', ['post' => $id]);
    }

    public function delete(Post $post){

        $post->delete();
        return redirect('/profile/'.auth()->user()->username)->with('success','Post successfully deleted!');
    }

    public function updatepost(Post $post, Request $request){
        $incommingRequest = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);
        $incommingRequest['body'] = strip_tags($incommingRequest['body']);
        $incommingRequest['title'] = strip_tags($incommingRequest['title']);

        $post->update($incommingRequest);

        return back()->with('success','Post successfully updated!');
    }

    public function showEditForm(Post $post){
        return view('edit-post',['post'=>$post]);
    }
}
