<?php

use App\Events\ChatMessage;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

Route::get('/admin-only', function (){

})->middleware('can:isAdmin');

// user routes
Route::get('/',[UserController::class, 'showCorrectHomePage'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register')->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/manage-avatar',[UserController::class, 'manageAvatar'])->name('manage-avatar')->middleware('auth');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('auth');
Route::get('/profile/{user:username}/followers',[UserController::class, 'profileFollowers'])->middleware('auth');
Route::get('/profile/{user:username}/following',[UserController::class, 'profileFollowing'])->middleware('auth');

// blog post routes
Route::get('/create-post',[PostController::class, 'showCreatePost'])->middleware('mustBeLoggedIn');
Route::post('/create-post',[PostController::class, 'createPost'])->middleware('auth');
Route::get('/post/{id}',[PostController::class, 'viewSinglePost'])->middleware('auth');

// profile related routes
Route::get('/profile/{user:username}', [UserController::class,'profile'])->middleware('auth');
Route::delete('/post/{post}',[PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit',[PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}',[PostController::class, 'updatepost'])->middleware('can:update,post');
Route::get('/search/{term}',[PostController::class, 'search']);

// follow related routes
Route::post('/create-follow/{user:username}',[FollowController::class,'createFollow'])->middleware('auth');
Route::post('/remove-follow/{user:username}',[FollowController::class,'removeFollow'])->middleware('auth');


// chat route
Route::post('/send-chat-message',function(Request $request){
    $formFields = $request->validate([
        'textvalue' => 'required|string',
    ]);

    if(!trim(strip_tags($formFields['textvalue']))){
        return response('Text value cannot be empty', 400);
    }
    broadcast(new ChatMessage([
        'username' => auth()->user()->username,
        'textvalue'=> strip_tags($request->textvalue),
        'avatar'=>auth()->user()->avatar
    ]))->toOthers();

    return response()->noContent();
})->middleware('auth');



// for debuging
Route::get('/phpinfo', function () {
    phpinfo();
});



Route::get('/debug-tmp', function () {
    dd([
        'ini_upload_tmp_dir' => ini_get('upload_tmp_dir'),
        'sys_temp_dir' => sys_get_temp_dir(),
        'is_writable' => is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()),
    ]);
});

