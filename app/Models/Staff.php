<?php

namespace App\Models;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
    use HasFactory;

    protected $table= 'staffs';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
