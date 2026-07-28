<?php

namespace App\Http\Services\Panel\Reports\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Report\ReportService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, ReportService $reports): View
    {
        $currency = strtoupper((string) ($request->query('currency_code') ?: ShardManager::currency()));

        $summary = $scope->isAdmin()
            ? $reports->fleetSummary($currency)
            : $reports->officeSummary((int) $scope->officeId(), $currency);

        return view('panel.reports.index', [
            'entity'   => $scope->guard(),
            'isAdmin'  => $scope->isAdmin(),
            'summary'  => $summary,
            'currency' => $currency,
        ]);
    }
}
