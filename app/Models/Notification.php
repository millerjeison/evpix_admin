<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $hidden = ["deleted_at", "created_at", "updated_at"];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notifications');
    }

}
