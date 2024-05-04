<?php

namespace App\Models;

use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatFile extends Model
{
    use HasFactory;

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function getFileUrlAttribute($value)
    {
        return url(Storage::url($value));
    }
}
