<?php

namespace App\Http\Services\Panel\Chat\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Chat\ChatService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\ChatConversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfficeChatReplyController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $conversation, ChatService $chat): RedirectResponse
    {
        $officeId = (int) $scope->officeId();

        $owns = ChatConversation::query()->where('id', $conversation)->where('office_id', $officeId)->exists();

        if (! $owns) {
            abort(404);
        }

        $body = trim((string) $request->input('body', ''));

        if ($body === '') {
            return back()->with('error', textByLanguage('الرسالة مطلوبة.', 'Message is required.'));
        }

        $chat->send($conversation, 'office', $officeId, $body);

        return back()->with('status', textByLanguage('تم إرسال الرسالة.', 'Message sent.'));
    }
}
