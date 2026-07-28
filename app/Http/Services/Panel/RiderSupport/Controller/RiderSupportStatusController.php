<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Const\Support\SupportActor;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RiderSupportStatusController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $ticket, RiderSupportService $support): RedirectResponse
    {
        $status = (string) $request->input('status', '');

        try {
            // The governed machine enforces the legal edge + the actor's authority;
            // an office may only govern its own tickets.
            if ($scope->isAdmin()) {
                $support->setStatus($ticket, $status, SupportActor::FLEETOS);
            } else {
                $support->officeSetStatus((int) $scope->officeId(), $ticket, $status);
            }
        } catch (DomainException $e) {
            return back()->with('error', textByLanguage('انتقال حالة غير مسموح.', 'Status change not allowed.'));
        }

        return back()->with('status', textByLanguage('تم تحديث الحالة.', 'Status updated.'));
    }
}
