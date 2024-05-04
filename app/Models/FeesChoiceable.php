<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeesChoiceable extends Model
{
    use HasFactory;
    protected $hidden = ["deleted_at", "created_at", "updated_at"];

    public function fees_type(){
        return $this->belongsTo(FeesType::class ,'fees_type_id');
    }
    protected $fillable = [
        'id',
        'student_id',
        'class_id',
        'fees_type_id',
        'is_due_charges',
        'total_amount',
        'session_year_id',
        'date',
        'payment_transaction_id',
        'status'
    ];
}
