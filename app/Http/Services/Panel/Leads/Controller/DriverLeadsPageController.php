<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\DriverJobApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DriverLeadsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $query = DriverJobApplication::query()->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return view('panel.leads.driver-leads', [
            'entity' => $scope->guard(),
            'applications' => $query->limit(200)->get(),
            'statusFilter' => $status,
            'counts' => [
                'pending' => DriverJobApplication::query()->where('status', 'pending')->count(),
                'approved' => DriverJobApplication::query()->where('status', 'approved')->count(),
                'rejected' => DriverJobApplication::query()->where('status', 'rejected')->count(),
                'total' => DriverJobApplication::query()->count(),
            ],
        ]);
    }
}
