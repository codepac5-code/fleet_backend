<?php

namespace App\Http\Services\Panel\Admin\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Models\StaffTwoFactor;
use Illuminate\View\View;
use Throwable;

/**
 * Platform-wide 2FA policy plus who is enrolled. Resetting an enrolment is the
 * lock-out escape hatch — a staff member who lost their phone re-enrols on next
 * sign-in instead of being stranded.
 */
class StaffSecurityPageController extends Controller
{
    public function __invoke(StaffIdentity $identity): View
    {
        try {
            $records = StaffTwoFactor::query()->orderBy('guard')->orderBy('staff_id')->get();
        } catch (Throwable $e) {
            $records = collect();
        }

        return view('panel.settings.security', [
            'requirement' => $identity->requirement(),
            'records' => $records,
        ]);
    }
}
