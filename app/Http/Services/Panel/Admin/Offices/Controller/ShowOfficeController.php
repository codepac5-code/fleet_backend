<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeAnalytics;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class ShowOfficeController extends Controller
{
    public function __invoke(int $office, EntityScope $scope, OfficeRepository $offices, OfficeAnalytics $analytics): View
    {
        $model = $offices->findOrFail($office);

        return view('panel.offices.show', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'office'   => $model,
            'counts'   => $analytics->counts($model),
            'overview' => $analytics->overview($model),
            'wallet'   => $analytics->wallet($model),
        ]);
    }
}
