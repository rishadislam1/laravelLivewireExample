<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function avatar(): Attribute{
        return Attribute::make(get: function($value){
            return $value?'/storage/avatars/'.$value:'/fallback-avatar.jpg';
        });
    }

    public function feedPosts(){
        return $this->hasManyThrough(Post::class,Follow::class,'user_id','user_id','id','followeduser');
    }

    public function followers(){
        return $this->hasMany(Follow::class, 'followeduser');
    }

    public function followingTheseUsers(){
        return $this->hasMany(Follow::class, 'user_id');
    }

    public function posts(){
        return $this->hasMany(Post::class,'user_id');
    }
}
