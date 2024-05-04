<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;
    protected $hidden = ["deleted_at","created_at","updated_at"];

    public function student(){
        return $this->belongsTo(Students::class ,'student_id')->with('user')->withTrashed();
    }
    public function session_year(){
        return $this->belongsTo(SessionYear::class,'session_year_id');
    }

    public function exam(){
        return $this->belongsTo(Exam::class,'exam_id');
    }

    public function class_section()
    {
        return $this->belongsTo(ClassSection::class ,'class_section_id')->with('class','section','class.medium','streams');
    }
}
