<?php

namespace App\Models;

use App\Models\Teacher;
use App\Models\ClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassTeacher extends Model
{
    use HasFactory;


    public function classSections()
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function classTeachers()
    {
        return $this->belongsTo(Teacher::class, 'class_teacher_id');
    }
}

