<?php

namespace App\Http\Services\Panel\Admin\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Models\StaffTwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResetStaffTwoFactorController extends Controller
{
    public function __invoke(Request $request, int $record, AuditLogService $audit): RedirectResponse
    {
        $entry = StaffTwoFactor::query()->find($record);

        if ($entry !== null) {
            $audit->record(
                'security.2fa_reset',
                'admin',
                (int) (auth('admin')->id() ?? 0),
                $entry->guard,
                $entry->staff_id,
                ['country' => $entry->country_code],
                $request->ip()
            );

            $entry->delete();
        }

        return back()->with('status', textByLanguage('تمت إعادة تعيين التحقق بخطوتين لهذا الحساب', 'Two-factor authentication reset for that account'));
    }
}
