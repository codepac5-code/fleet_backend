<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DriverSafetyEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Staff action on a driver-safety / SOS event: acknowledge (someone is on it) or
 * resolve (handled). The board was view-only — a live emergency had no way to be
 * marked as being dealt with. Who/when is captured in the per-country audit log
 * (there is no resolved_at column and this needs no migration); the appended note
 * keeps any resolution detail on the event itself.
 */
class UpdateDriverSafetyStatusController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $event, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:acknowledged,resolved'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $conn = TenantConnection::current();

        $query = DriverSafetyEvent::on($conn)->whereKey($event);

        // An office may only act on its OWN drivers' events; admin on any.
        if ($scope->isOffice()) {
            $query->where('office_id', (int) $scope->officeId());
        }

        $row = $query->first();

        if ($row === null) {
            return back()->with('error', textByLanguage('الحدث غير موجود.', 'Event not found.'));
        }

        $row->setConnection($conn);
        $row->status = $data['status'];

        if (! empty($data['note'])) {
            $stamp = now()->toDateTimeString();
            $row->note = trim(($row->note ? $row->note . "\n" : '') . '[' . $stamp . '] ' . $data['note']);
        }

        $row->save();

        $audit->record(
            'safety.' . $data['status'],
            $scope->guard(),
            (int) ($scope->user()->id ?? 0),
            'driver_safety_event',
            (int) $row->id,
            ['kind' => $row->kind, 'driver_id' => $row->driver_id],
            $request->ip()
        );

        return back()->with('status', $data['status'] === 'resolved'
            ? textByLanguage('تم وسم الحدث كمُعالَج.', 'Event marked resolved.')
            : textByLanguage('تم استلام الحدث.', 'Event acknowledged.'));
    }
}
