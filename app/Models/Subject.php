<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Subject extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $hidden = ["deleted_at", "created_at", "updated_at"];

    public function medium() {
        return $this->belongsTo(Mediums::class)->withTrashed();
    }

    public function scopeSubjectTeacher($query) {
        $user = Auth::user();
        if ($user->hasRole('Teacher')) {
            $subjects_ids = $user->teacher->subjects()->pluck('subject_id');
            return $query->whereIn('id', $subjects_ids);
        }
        return $query;
    }

    //Getter Attributes
    public function getImageAttribute($value) {
        return url(Storage::url($value));
    }
}
