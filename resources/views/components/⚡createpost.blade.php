<?php

use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public $title;
    public $body;

    public function create()
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }
        $incommingRequest = $this->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incommingRequest['title']);
        $incomingFields['body'] = strip_tags($incommingRequest['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);
//
//        dispatch(new SendNewpostEmail(['sendTo']=>auth()->user()->email,'name'=>auth()->user()->name));


        session()->flash('success', 'New post successfully created');
        return $this->redirect("/post/{$newPost->id}", navigate: true);
    }
};
?>

<div>
    <form wire:submit="create" action="#" method="POST">
        @csrf
        <div class="form-group">
            <label for="post-title" class="text-muted mb-1"><small>Title</small></label>
            <input wire:model="title" required value="{{old('title')}}" name="title" id="post-title"
                   class="form-control form-control-lg form-control-title" type="text" placeholder=""
                   autocomplete="off"/>
            @error('title')
            <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="post-body" class="text-muted mb-1"><small>Body Content</small></label>
            <textarea wire:model="body" required name="body" id="post-body"
                      class="body-content tall-textarea form-control" type="text">{{old('body')}}</textarea>
            @error('body')
            <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
            @enderror
        </div>

        <button class="btn btn-primary">Save New Post</button>
    </form>
</div>
