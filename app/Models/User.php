<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\Notification;
use App\Models\UserNotification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    // protected $appends =[
    //     'original_image'
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        "deleted_at",
        "created_at",
        "updated_at"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function student() {
        return $this->hasOne(Students::class, 'user_id', 'id');
    }

    public function parent() {
        return $this->hasOne(Parents::class, 'user_id', 'id');
    }

    public function teacher() {
        return $this->hasOne(Teacher::class, 'user_id', 'id');
    }

    public function staff() {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    //Getter Attributes
    public function getImageAttribute($value) {
        return url(Storage::url($value));
    }

    // //Getter Attributes
    // public function getOriginalImageAttribute() {
    //     return $this->getRawOriginal('image');
    // }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notifications');
    }

    public function messages()
    {
        return $this->morphMany(ChatMessage::class, 'modal');
    }

}
