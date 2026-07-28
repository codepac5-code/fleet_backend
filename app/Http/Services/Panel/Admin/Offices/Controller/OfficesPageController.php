<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, OfficeRepository $offices): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('panel.offices.index', [
            'entity'  => $scope->guard(),
            'user'    => $scope->user(),
            'search'  => $search,
            'offices' => $offices->paginate($search ?: null),
        ]);
    }
}
