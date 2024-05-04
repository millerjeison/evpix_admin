<?php

namespace App\Models;

use App\Models\ChatRoom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMember extends Model
{
    use HasFactory;

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }
}
