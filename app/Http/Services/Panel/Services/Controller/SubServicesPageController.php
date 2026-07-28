<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class SubServicesPageController extends Controller
{
    public function __invoke(int $service, EntityScope $scope, ServiceRepository $services, SubServiceRepository $subServices): View
    {
        $model = $services->findOrFail($service);

        return view('panel.services.sub-services.index', [
            'entity'      => $scope->guard(),
            'user'        => $scope->user(),
            'service'     => $model,
            'subServices' => $subServices->forService($model->id),
        ]);
    }
}
