<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceAnalytics;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class ShowServiceController extends Controller
{
    public function __invoke(int $service, EntityScope $scope, ServiceRepository $services, SubServiceRepository $subServices, ServiceAnalytics $analytics): View
    {
        $model = $services->findOrFail($service);

        return view('panel.services.show', [
            'entity'      => $scope->guard(),
            'user'        => $scope->user(),
            'service'     => $model,
            'subServices' => $subServices->forService($model->id),
            'overview'    => $analytics->overview($model),
        ]);
    }
}
