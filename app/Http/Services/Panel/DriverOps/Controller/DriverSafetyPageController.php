<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\DriverSafetyEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DriverSafetyPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $kind = $request->query('kind');
        $kind = $kind !== null && $kind !== '' ? (string) $kind : null;

        $scoped = fn () => DriverSafetyEvent::query()
            ->when($scope->isOffice(), fn ($q) => $q->where('office_id', (int) $scope->officeId()));

        $query = $scoped()->orderByDesc('id');

        if ($kind !== null) {
            $query->where('kind', $kind);
        }

        return view('panel.driver-safety.index', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'events' => $query->limit(100)->get(),
            'kindFilter' => $kind,
            'counts' => [
                'total' => $scoped()->count(),
                'sos' => $scoped()->where('kind', 'sos')->count(),
                'reports' => $scoped()->where('kind', 'report')->count(),
                'today' => $scoped()->where('created_at', '>=', now()->startOfDay())->count(),
            ],
        ]);
    }
}
