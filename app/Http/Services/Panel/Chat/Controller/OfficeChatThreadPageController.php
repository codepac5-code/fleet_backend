<?php

namespace App\Http\Services\Panel\Chat\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Chat\ChatService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\ChatConversation;
use Illuminate\Contracts\View\View;

class OfficeChatThreadPageController extends Controller
{
    public function __invoke(EntityScope $scope, int $conversation, ChatService $chat): View
    {
        $officeId = (int) $scope->officeId();

        $conv = ChatConversation::query()->where('id', $conversation)->where('office_id', $officeId)->first();

        if ($conv === null) {
            abort(404);
        }

        $messages = array_map(fn ($m) => [
            'id'          => (int) $m->id,
            'sender_type' => $m->sender_type,
            'body'        => $m->body,
            'created_at'  => $m->created_at,
        ], $chat->messages($conversation, 50, null));

        return view('panel.chat.thread', [
            'entity'       => $scope->guard(),
            'conversation' => [
                'id'         => (int) $conv->id,
                'user_id'    => (int) $conv->user_id,
                'booking_id' => $conv->booking_id !== null ? (int) $conv->booking_id : null,
            ],
            'messages'     => $messages,
        ]);
    }
}
