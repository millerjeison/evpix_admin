<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class Shift extends Model
{
    use HasFactory;
    use softDeletes;
    protected $hidden = ["deleted_at", "created_at", "updated_at"];
}
