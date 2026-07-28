<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Const\Support\SupportActor;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Escalate an office-desk ticket up to the FleetOS platform desk. Governed by
 * {@see RiderSupportService::escalate} — only an office's own, non-terminal,
 * office-layer ticket can be handed up.
 */
class RiderSupportEscalateController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $ticket, RiderSupportService $support): RedirectResponse
    {
        $note = trim((string) $request->input('note', ''));

        try {
            if ($scope->isAdmin()) {
                // A platform agent may escalate any office ticket directly.
                $support->escalate($ticket, SupportActor::FLEETOS, $note !== '' ? $note : null);
            } else {
                $support->officeEscalate((int) $scope->officeId(), $ticket, $note !== '' ? $note : null);
            }
        } catch (DomainException $e) {
            return back()->with('error', textByLanguage('تعذّر التصعيد.', 'Could not escalate.'));
        }

        return back()->with('status', textByLanguage('تم تصعيد التذكرة إلى الدعم المركزي.', 'Ticket escalated to central support.'));
    }
}
