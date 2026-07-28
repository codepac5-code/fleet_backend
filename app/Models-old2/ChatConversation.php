<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use ChatConversation;
use ChatMessage;

class ChatConversation extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'chat_conversations';

    protected $fillable = ['user_id', 'office_id', 'booking_id', 'last_message_at'];

    protected $casts = [
        'user_id' => 'integer',
        'office_id' => 'integer',
        'booking_id' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
}
