<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $hidden = ["deleted_at","created_at","updated_at"];
    public function classes() {
        return $this->belongsToMany(ClassSchool::class, 'class_sections', 'section_id', 'class_id');
    }
}
