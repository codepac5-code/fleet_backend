<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Classes\Subscription\PlanUsageService;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Drivers\Request\StoreDriverRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class StoreDriverController extends Controller
{
    public function __invoke(StoreDriverRequest $request, EntityScope $scope, DriverRepository $drivers, PlanUsageService $usage, PlanOverageService $overage): RedirectResponse
    {
        $data = $request->validated();

        if (! $scope->isAdmin()) {
            $data['officeId'] = $scope->officeId();
        }

        // Plan driver-limit gate (subscription regions only; no-op off-plan). A
        // HARD limit (no overage price) blocks; a plan that permits paid overage
        // lets it through and ACCRUES the extra-driver fee to the invoice.
        $officeId = (int) ($data['officeId'] ?? 0);
        $extraCharge = $officeId > 0 ? $usage->extraDriverCharge($officeId) : null;
        $overLimit = $officeId > 0 && $usage->driverAddWouldExceed($officeId);

        if ($overLimit && $extraCharge === null) {
            return back()->withInput()->with('error', textByLanguage(
                'تم بلوغ حد السائقين في خطة المكتب — رقِّ الخطة لإضافة المزيد',
                'Office plan driver limit reached — upgrade the plan to add more'
            ));
        }

        $driver = $drivers->create($data);

        $overageNote = null;
        if ($overLimit && $extraCharge !== null) {
            $usageSnapshot = $usage->forOffice($officeId);
            $overage->recordDriverOverage($officeId, (int) $driver->id, $extraCharge, (string) ($usageSnapshot['currency'] ?? ''));
            $overageNote = textByLanguage(
                'تمّت الإضافة فوق حد الخطة — أُضيفت رسوم سائق إضافي للفاتورة.',
                'Added over the plan limit — an extra-driver fee was accrued to the invoice.'
            );
        }

        return redirect()
            ->route("panel.{$scope->guard()}.driver.index")
            ->with('status', textByLanguage('تمت إضافة السائق بنجاح', 'Driver created successfully'))
            ->with($overageNote !== null ? 'warning' : '_none', $overageNote);
    }
}
