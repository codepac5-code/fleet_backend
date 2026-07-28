<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ServicesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, ServiceRepository $services): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('panel.services.index', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'search'   => $search,
            'services' => $services->paginate($search ?: null),
        ]);
    }
}
