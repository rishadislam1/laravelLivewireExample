<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

class UserController extends Controller
{
    public function showCorrectHomePage()
    {
        if (auth()->check()) {
            return view('homepage-feed',['posts'=> auth()->user()->feedPosts()->latest()->paginate(4)]);
        } else {
            $postCount = Cache::remember('postCount',20, function () {
                return Post::count();
            });
            return view('homepage',['postCount'=>$postCount]);
        }
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'You have been logged out!');
    }

    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:255', Rule::unique('users', 'username')],
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $incomingFields['password'] = Hash::make($incomingFields['password']);

        User::create($incomingFields);

        return redirect('/')->with('success', 'You have been registered!');
    }

    public function loginApi(Request $request){
        $incomingFields = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if(auth()->attempt($incomingFields)){
            $user = User::where('username',$incomingFields['username'])->first();
            $token = $user->createToken($incomingFields['username'])->plainTextToken;
            return $token;
        }
        return '';
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => ['required', 'min:3', 'max:255'],
            'loginpassword' => 'required|string',
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'Welcome Back!');
        } else {
            return redirect('/')->with('error', 'Login Failed!');
        }
    }

    private function getSharedData($user){
//        $user = User::where('username', $username)->firstOrFail();

        $currentlyFollowing=0;
        if(auth()->check()) {
            $currentlyFollowing = Follow::where([['user_id','=',auth()->user()->id],['followeduser','=',$user->id]])->count();
        }

        View::share('sharedData',[
            'username' => $user->username,
            'postCount' => $user->posts()->count(),
            'user' => $user,
            'currentlyFollowing' => $currentlyFollowing,
            'avatar'=>$user->avatar,
            'followerCount'=>$user->followers()->count(),
            'followingCount'=>$user->followingTheseUsers()->count(),
        ]);
    }

    public function profile(User $user)
    {

        $this->getSharedData($user);
        return view('profile', [

            'posts' => $user->posts()->latest()->get()


        ]);
    }

    public function profileFollowers(User $user){
        $this->getSharedData($user);

        return view('profile-followers', [ 'followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowing(User $user){
       $this->getSharedData($user);

        return view('profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }

    public function manageAvatar()
    {
        return view('avatar-photo');
    }

    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        $fileName = $user->id . "_" . uniqid() . ".jpg";

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decode($request->file('avatar')->getRealPath());

        $imgData = $image->cover(120, 120)->encode(new JpegEncoder(quality: 90));

        Storage::disk('public')->put('avatars/' . $fileName, $imgData);

        $oldAvatar = $user->avatar;

        $user->avatar = $fileName;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::disk('public')->delete(str_replace("/storage/", "", $oldAvatar));
        }

        return back()->with('success', 'You have successfully updated your avatar!');
    }




}
