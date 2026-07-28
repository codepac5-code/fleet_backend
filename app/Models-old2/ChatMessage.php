<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use ChatMessage;

class ChatMessage extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'chat_messages';

    protected $fillable = ['conversation_id', 'sender_type', 'sender_id', 'body', 'read_at', 'created_at'];

    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id' => 'integer',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
