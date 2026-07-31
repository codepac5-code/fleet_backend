<?php

namespace App\Http\Services\Panel\Admin\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveSecurityPolicyController extends Controller
{
    public function __invoke(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'requirement' => ['nullable', 'in:,admin,all'],
        ]);

        $requirement = (string) ($data['requirement'] ?? '');

        SiteSetting::put(StaffIdentity::REQUIRE_KEY, $requirement);

        $audit->record('security.policy_changed', 'admin', (int) (auth('admin')->id() ?? 0), null, null, ['requirement' => $requirement], $request->ip());

        return back()->with('status', textByLanguage('تم حفظ سياسة الأمان', 'Security policy saved'));
    }
}
