<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\Office;
use App\Models\OfficeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Approving a website registration request now PROVISIONS the office account on
 * the ACTIVE country's shard — it used to only flip a status label, leaving an
 * admin to retype everything into the "new office" form. Mirrors the driver
 * application flow (approve → create the real record + audit).
 *
 * The generated password is shown to the reviewer ONCE; nothing stores it in
 * clear text, so a lost password is a reset, not a lookup.
 */
class ApproveOfficeRequestController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeRepository $offices, AuditLogService $audit, int $officeRequest): RedirectResponse
    {
        $record = OfficeRequest::query()->findOrFail($officeRequest);

        if ($request->input('decision') === 'reject') {
            $record->status = 'rejected';
            $record->save();

            return back()->with('status', textByLanguage('تم رفض الطلب.', 'Request rejected.'));
        }

        $connection = TenantConnection::current();
        $node = ShardManager::current();

        if ($node === null) {
            return back()->with('error', textByLanguage(
                'اختر دولة محدّدة من المبدّل قبل قبول الطلب — الحساب يُنشأ داخل قاعدة تلك الدولة.',
                'Pick a single country in the switcher before approving — the account is created inside that country\'s database.'
            ));
        }

        // Idempotent: an email already registered in this country means the
        // office exists, so just mark the request approved and say so.
        $existing = Office::on($connection)->where('email', $record->email)->first();

        if ($existing !== null) {
            $record->status = 'approved';
            $record->save();

            return back()->with('status', textByLanguage(
                'هذا البريد مسجّل بالفعل كمكتب في هذه الدولة — تم تعليم الطلب كمقبول.',
                'That email already belongs to an office in this country — the request was marked approved.'
            ));
        }

        $password = Str::password(12, true, true, false);

        $office = $offices->create([
            'officeName' => $record->office_name,
            'displayName' => $record->contact_name ?: $record->office_name,
            'email' => $record->email,
            'contactNumber' => $record->phone,
            'city' => $record->city,
            'country' => $node->country_code,
            'region' => $node->name,
            'status' => 1,
            'password' => $password,
        ]);

        $record->status = 'approved';
        $record->save();

        $audit->record(
            'office.provisioned',
            'admin',
            $scope->user()?->id,
            'office_request',
            (int) $record->id,
            ['office_id' => (int) $office->id, 'country' => $node->country_code],
            $request->ip()
        );

        return back()
            ->with('status', textByLanguage('تم إنشاء حساب المكتب.', 'Office account created.'))
            ->with('office_credentials', [
                'name' => $office->officeName,
                'email' => $office->email,
                'password' => $password,
                'country' => strtoupper((string) $node->country_code),
            ]);
    }
}
