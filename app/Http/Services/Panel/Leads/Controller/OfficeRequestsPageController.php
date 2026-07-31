<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\OfficeRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficeRequestsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $query = OfficeRequest::query()->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return view('panel.leads.office-requests', [
            'entity' => $scope->guard(),
            'requests' => $query->limit(200)->get(),
            'statusFilter' => $status,
            'counts' => [
                'new' => OfficeRequest::query()->where('status', 'new')->count(),
                'reviewed' => OfficeRequest::query()->where('status', 'reviewed')->count(),
                'approved' => OfficeRequest::query()->where('status', 'approved')->count(),
                'total' => OfficeRequest::query()->count(),
            ],
        ]);
    }
}
