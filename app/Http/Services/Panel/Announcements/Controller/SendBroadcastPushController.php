<?php

namespace App\Http\Services\Panel\Announcements\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Jobs\SendBroadcastPush;
use App\Models\DeviceToken;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SendBroadcastPushController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'audience' => ['required', 'in:riders,drivers'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        // Offices may only message their own drivers, never riders.
        if (! $scope->isAdmin() && $data['audience'] !== 'drivers') {
            return back()->with('error', textByLanguage('غير مسموح', 'Not allowed'));
        }

        $ownerType = $data['audience'] === 'riders' ? 'user' : 'driver';

        // Tokens resolved on the ACTIVE shard → this country's devices only.
        $query = DeviceToken::query()->where('owner_type', $ownerType)->whereNotNull('token');

        if (! $scope->isAdmin()) {
            $query->whereIn('owner_id', Driver::on(TenantConnection::current())->where('officeId', (int) $scope->officeId())->pluck('id')->all());
        }

        $tokens = $query->pluck('token')->filter()->unique()->values()->all();

        if ($tokens === []) {
            return back()->with('error', textByLanguage('لا توجد أجهزة للإرسال إليها', 'No devices to send to'));
        }

        foreach (array_chunk($tokens, 500) as $chunk) {
            SendBroadcastPush::dispatch($chunk, $data['title'], $data['body']);
        }

        $audit->record(
            'announcement.push_sent',
            $scope->isAdmin() ? 'admin' : 'office',
            $scope->isAdmin() ? null : $scope->officeId(),
            'announcement',
            null,
            ['audience' => $data['audience'], 'devices' => count($tokens), 'title' => $data['title']],
            $request->ip()
        );

        return back()->with('status', textByLanguage(
            'تمّت جدولة الإرسال إلى ' . count($tokens) . ' جهاز',
            'Queued to ' . count($tokens) . ' devices'
        ));
    }
}
