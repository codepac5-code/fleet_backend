<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Export\CsvExport;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDriversController extends Controller
{
    public function __invoke(EntityScope $scope): StreamedResponse
    {
        // Active country shard + office scope → the export mirrors exactly what
        // this operator can see in the list; never another country's drivers.
        $query = Driver::on(TenantConnection::current())->newQuery();
        $scope->scopeByOffice($query);

        $rows = $query->orderBy('id')
            ->get(['id', 'firstName', 'lastName', 'phoneNumber', 'officeId', 'isActive', 'is_online'])
            ->map(fn ($d) => [
                $d->id,
                trim(($d->firstName ?? '') . ' ' . ($d->lastName ?? '')),
                $d->phoneNumber,
                $d->officeId,
                $d->isActive ? 'active' : 'suspended',
                $d->is_online ? 'online' : 'offline',
            ]);

        return CsvExport::stream(
            'drivers.csv',
            ['ID', 'Name', 'Phone', 'Office', 'Status', 'Presence'],
            $rows
        );
    }
}
