<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateSubServiceController extends Controller
{
    public function __invoke(int $service, EntityScope $scope, ServiceRepository $services): View
    {
        return view('panel.services.sub-services.form', [
            'entity'     => $scope->guard(),
            'user'       => $scope->user(),
            'service'    => $services->findOrFail($service),
            'subService' => null,
        ]);
    }
}
