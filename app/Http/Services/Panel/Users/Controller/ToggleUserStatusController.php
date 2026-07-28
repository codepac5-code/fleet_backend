<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ToggleUserStatusController extends Controller
{
    public function __invoke(int $user, Request $request, UserRepository $users, AuditLogService $audit): RedirectResponse
    {
        $model = $users->findOrFail($user);
        $reason = trim((string) $request->input('reason'));
        $users->toggleStatus($model, $reason);

        // isActive=0 blocks the rider from opening new rides (guarded in
        // RideBookingService::create). Users are global, so the block applies in
        // every country; the audit is best-effort (per-country context).
        $blocked = ! (bool) $model->isActive;

        $audit->record(
            $blocked ? 'user.blocked' : 'user.reinstated',
            'admin',
            null,
            'user',
            (int) $model->id,
            $blocked && $reason !== '' ? ['reason' => $reason] : [],
            $request->ip()
        );

        return back()->with('status', $blocked
            ? textByLanguage('تم حظر الراكب — لا يستطيع حجز رحلات جديدة', 'Rider blocked — cannot book new rides')
            : textByLanguage('تمت إعادة تفعيل الراكب', 'Rider reinstated'));
    }
}
