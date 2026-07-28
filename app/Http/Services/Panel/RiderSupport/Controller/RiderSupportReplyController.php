<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RiderSupportReplyController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $ticket, RiderSupportService $support): RedirectResponse
    {
        $body = trim((string) $request->input('body', ''));

        if ($body === '') {
            return back()->with('error', textByLanguage('الرسالة مطلوبة.', 'Message is required.'));
        }

        try {
            $agentId = (int) ($scope->user()->id ?? 0);

            if ($scope->isAdmin()) {
                $support->staffReply($ticket, 'fleet', $agentId, $body);
            } else {
                $support->officeReply((int) $scope->officeId(), $ticket, $agentId, $body);
            }
        } catch (DomainException $e) {
            return back()->with('error', textByLanguage('تعذّر إرسال الردّ.', 'Could not send reply.'));
        }

        return back()->with('status', textByLanguage('تم إرسال الردّ.', 'Reply sent.'));
    }
}
