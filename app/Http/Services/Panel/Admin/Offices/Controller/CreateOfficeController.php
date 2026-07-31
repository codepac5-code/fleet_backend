<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateOfficeController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.offices.form', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'office' => null,
            // The office is set up under one or more main services; everything
            // it may price hangs off that choice.
            'services' => \App\Models\Service::on(\App\Http\Services\Panel\Shared\Tenant\TenantConnection::current())
                ->where('status', 1)->orderBy('id')->get(['id', 'title', 'title_en', 'travel_service']),
            'assignedServiceIds' => [],
            'defaultFleetRate' => \App\Http\Core\Const\Subscription\CommissionDefaults::fleetRate(),
        ]);
    }
}
