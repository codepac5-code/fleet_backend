<?php

namespace App\Http\Services\Panel\Announcements\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DeviceToken;
use App\Models\Driver;
use Illuminate\View\View;

class PushComposerPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        // DeviceToken is per-country (ResolvesTenantConnection): every count and
        // send below only ever touches the active country's devices.
        $drivers = DeviceToken::query()->where('owner_type', 'driver');

        if (! $scope->isAdmin()) {
            $drivers->whereIn('owner_id', $this->officeDriverIds((int) $scope->officeId()));
        }

        return view('panel.announcements.index', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'riderCount' => $scope->isAdmin() ? DeviceToken::query()->where('owner_type', 'user')->count() : 0,
            'driverCount' => $drivers->count(),
        ]);
    }

    private function officeDriverIds(int $officeId): array
    {
        return Driver::on(TenantConnection::current())->where('officeId', $officeId)->pluck('id')->all();
    }
}
