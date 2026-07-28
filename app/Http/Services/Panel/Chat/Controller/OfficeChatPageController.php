<?php

namespace App\Http\Services\Panel\Chat\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Chat\ChatService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class OfficeChatPageController extends Controller
{
    public function __invoke(EntityScope $scope, ChatService $chat): View
    {
        $conversations = array_map(fn ($c) => [
            'id'              => (int) $c->id,
            'user_id'         => (int) $c->user_id,
            'booking_id'      => $c->booking_id !== null ? (int) $c->booking_id : null,
            'last_message_at' => $c->last_message_at,
        ], $chat->conversationsForOffice((int) $scope->officeId()));

        return view('panel.chat.index', [
            'entity'        => $scope->guard(),
            'conversations' => $conversations,
        ]);
    }
}
