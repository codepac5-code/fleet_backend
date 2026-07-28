<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Classes\Subscription\PlanUsageService;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\DriverApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewDriverApplicationController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, DriverRepository $drivers, AuditLogService $audit, PlanUsageService $usage, PlanOverageService $overage, int $application): RedirectResponse
    {
        $decision = $request->input('decision') === 'reject' ? 'rejected' : 'approved';

        $query = DriverApplication::query()->where('id', $application);

        if ($scope->isOffice()) {
            $query->where('office_id', (int) $scope->officeId());
        }

        $app = $query->first();

        if ($app === null) {
            abort(404);
        }

        if ($decision === 'rejected') {
            $app->status = 'rejected';
            $app->save();

            return back()->with('status', textByLanguage('تم رفض الطلب.', 'Application rejected.'));
        }

        // Same plan driver-limit gate as the manual add path (StoreDriverController):
        // a HARD limit (no overage price) blocks the approval; a plan that permits
        // paid overage lets it through and accrues the extra-driver fee. Checked
        // BEFORE marking approved so a blocked application stays pending.
        $officeId = (int) $app->office_id;
        $extraCharge = $usage->extraDriverCharge($officeId);
        $overLimit = $usage->driverAddWouldExceed($officeId);

        if ($overLimit && $extraCharge === null) {
            return back()->with('error', textByLanguage(
                'تم بلوغ حد السائقين في خطة المكتب — رقِّ الخطة للقبول',
                'Office plan driver limit reached — upgrade the plan to approve'
            ));
        }

        $app->status = 'approved';
        $app->save();

        // Approving now PROVISIONS the driver on this country's shard — no more
        // "go create it manually". Idempotent: skip if this application was
        // already approved, or a driver already exists with the same phone.
        $driver = $this->provision($app, $drivers);

        if ($driver !== null) {
            $overageNote = null;
            if ($overLimit && $extraCharge !== null) {
                $snapshot = $usage->forOffice($officeId);
                $overage->recordDriverOverage($officeId, (int) $driver->id, $extraCharge, (string) ($snapshot['currency'] ?? ''));
                $overageNote = textByLanguage(
                    'أُضيفت رسوم سائق إضافي للفاتورة (فوق حد الخطة).',
                    'An extra-driver fee was accrued to the invoice (over the plan limit).'
                );
            }

            $audit->record(
                'driver.provisioned',
                $scope->isAdmin() ? 'admin' : 'office',
                $scope->isAdmin() ? null : $scope->officeId(),
                'driver',
                (int) $driver->id,
                ['application_id' => (int) $app->id],
                $request->ip()
            );

            return back()
                ->with('status', textByLanguage('تم قبول الطلب وإنشاء السائق.', 'Approved — driver created.'))
                ->with($overageNote !== null ? 'warning' : '_none', $overageNote);
        }

        return back()->with('status', textByLanguage('تم قبول الطلب (السائق موجود مسبقاً).', 'Approved (driver already exists).'));
    }

    private function provision(DriverApplication $app, DriverRepository $drivers): ?Driver
    {
        $phone = trim((string) $app->phone);

        if ($phone === '') {
            return null;
        }

        $exists = Driver::on(TenantConnection::current())->where('phoneNumber', $phone)->exists();

        if ($exists) {
            return null;
        }

        [$first, $last] = $this->names($app);

        return $drivers->create([
            'firstName' => $first,
            'lastName' => $last,
            'phoneNumber' => $phone,
            'officeId' => $app->office_id,
            'password' => Str::random(24),
            'isActive' => 1,
        ]);
    }

    private function names(DriverApplication $app): array
    {
        $first = trim((string) ($app->first_name ?? ''));
        $last = trim((string) ($app->last_name ?? ''));

        if ($first === '' && $last === '') {
            $parts = preg_split('/\s+/', trim((string) ($app->name ?? '')), 2);
            $first = $parts[0] ?? '';
            $last = $parts[1] ?? '';
        }

        return [$first, $last];
    }
}
