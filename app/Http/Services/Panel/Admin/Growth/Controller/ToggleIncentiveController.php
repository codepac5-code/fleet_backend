<?php

namespace App\Http\Services\Panel\Admin\Growth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DriverIncentive;
use Illuminate\Http\RedirectResponse;

/**
 * Pausing a rule stops it counting and paying immediately; progress rows are
 * kept so a paused-then-resumed promotion does not wipe what drivers earned.
 */
class ToggleIncentiveController extends Controller
{
    public function __invoke(int $incentive): RedirectResponse
    {
        $rule = DriverIncentive::on(TenantConnection::current())->find($incentive);

        if ($rule !== null) {
            $rule->is_active = ! $rule->is_active;
            $rule->save();
        }

        return back()->with('status', textByLanguage('تم تحديث حالة الحافز', 'Incentive status updated'));
    }
}
