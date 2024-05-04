<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalProgram extends Model
{
    use HasFactory;

    public function getImageAttribute($value){
        return url(Storage::url($value));
    }
}
