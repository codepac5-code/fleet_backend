<?php

namespace App\Http\Services\Panel\Announcements\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\DeviceToken;
use App\Models\Driver;
use App\Models\InfrastructureNode;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PushComposerPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        // An announcement is aimed at ONE country's devices. The composer used
        // to silently address whichever country the switcher happened to be on
        // — including "all countries", where the counts made no sense. The
        // target is now chosen explicitly.
        $countries = $this->countries($scope);
        $targetId = (int) ($request->query('country') ?: 0);
        $target = $countries->firstWhere('id', $targetId) ?? $countries->first();

        if ($target !== null) {
            try {
                ShardManager::activate($target);
            } catch (Throwable $e) {
            }
        }

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
            'countries' => $countries,
            'targetCountry' => $target,
        ]);
    }

    /** Countries an admin may address; an office only ever addresses its own. */
    private function countries(EntityScope $scope)
    {
        if (! $scope->isAdmin()) {
            return collect();
        }

        try {
            return InfrastructureNode::query()
                ->where('type', 'country')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (Throwable $e) {
            return collect();
        }
    }

    private function officeDriverIds(int $officeId): array
    {
        return Driver::on(TenantConnection::current())->where('officeId', $officeId)->pluck('id')->all();
    }
}
