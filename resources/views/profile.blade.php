<x-profile-component :sharedData="$sharedData" docTitle="{{$sharedData['username']}}'s Profile">
    <div class="list-group">
        @foreach($posts as $post)
            <a wire:navigate href="/post/{{$post->id}}" class="list-group-item list-group-item-action">
                <img class="avatar-tiny" src="{{$post->user->avatar}}" />
                <strong>{{$post->title}}</strong> on {{$post->created_at}}
            </a>
        @endforeach
    </div>
</x-profile-component>
