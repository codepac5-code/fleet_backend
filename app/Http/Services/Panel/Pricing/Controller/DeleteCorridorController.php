<?php

namespace App\Http\Services\Panel\Pricing\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\TravelRoutes;
use Illuminate\Http\RedirectResponse;

class DeleteCorridorController extends Controller
{
    public function __invoke(EntityScope $scope, int $route): RedirectResponse
    {
        $conn = TenantConnection::current();

        $query = TravelRoutes::on($conn)->whereKey($route);
        // An office may only delete its OWN corridor; admin may delete any.
        $scope->scopeByOffice($query, 'officeId');

        $row = $query->first();

        if ($row === null) {
            return back()->with('error', textByLanguage('الخط غير موجود.', 'Corridor not found.'));
        }

        $row->delete();

        return back()->with('status', textByLanguage('تم حذف الخط.', 'Corridor removed.'));
    }
}
