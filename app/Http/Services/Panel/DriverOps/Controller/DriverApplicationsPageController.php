<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\DriverApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DriverApplicationsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = (string) $request->query('status', 'pending');
        $status = $status !== '' ? $status : null;

        $query = DriverApplication::query()->orderByDesc('id');

        if ($scope->isOffice()) {
            $query->where('office_id', (int) $scope->officeId());
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return view('panel.driver-applications.index', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'applications' => $query->limit(100)->get(),
            'statusFilter' => $status,
        ]);
    }
}
