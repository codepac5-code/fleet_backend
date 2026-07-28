<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RiderSupportPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, RiderSupportService $support): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;
        $category = $request->query('category');
        $category = $category !== null && $category !== '' ? (string) $category : null;

        $tickets = $scope->isAdmin()
            ? $support->fleetTickets($status, $category)
            : $support->officeTickets((int) $scope->officeId(), $status);

        return view('panel.rider-support.index', [
            'entity'         => $scope->guard(),
            'isAdmin'        => $scope->isAdmin(),
            'tickets'        => $tickets,
            'statusFilter'   => $status,
            'categoryFilter' => $scope->isAdmin() ? $category : null,
        ]);
    }
}
