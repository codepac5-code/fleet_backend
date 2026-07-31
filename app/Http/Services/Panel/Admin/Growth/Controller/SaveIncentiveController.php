<?php

namespace App\Http\Services\Panel\Admin\Growth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DriverIncentive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaveIncentiveController extends Controller
{
    public function __invoke(Request $request, AuditLogService $audit, ?int $incentive = null): RedirectResponse
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:120'],
            'name_ar' => ['required', 'string', 'max:120'],
            'window' => ['required', Rule::in(DriverIncentive::WINDOWS)],
            'target_rides' => ['required', 'integer', 'min:1', 'max:1000'],
            'reward' => ['required', 'numeric', 'min:0', 'max:100000'],
        ]);

        $conn = TenantConnection::current();
        $rule = $incentive !== null
            ? DriverIncentive::on($conn)->findOrFail($incentive)
            : (new DriverIncentive())->setConnection($conn);

        $rule->name_en = $data['name_en'];
        $rule->name_ar = $data['name_ar'];
        $rule->window = $data['window'];
        $rule->target_rides = (int) $data['target_rides'];
        $rule->reward_minor = (int) round(((float) $data['reward']) * 100);

        if ($incentive === null) {
            $rule->is_active = true;
        }

        $rule->save();

        $audit->record(
            $incentive === null ? 'incentive.created' : 'incentive.updated',
            'admin',
            (int) (auth('admin')->id() ?? 0),
            'driver_incentive',
            (int) $rule->id,
            ['target' => $rule->target_rides, 'reward_minor' => $rule->reward_minor, 'window' => $rule->window],
            $request->ip()
        );

        return back()->with('status', $incentive !== null
            ? textByLanguage('تم تحديث الحافز', 'Incentive updated')
            : textByLanguage('تمت إضافة الحافز', 'Incentive added'));
    }
}
