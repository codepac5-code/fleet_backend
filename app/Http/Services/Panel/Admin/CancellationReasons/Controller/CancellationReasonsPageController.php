<?php

namespace App\Http\Services\Panel\Admin\CancellationReasons\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\CancellationReason;
use Illuminate\View\View;

class CancellationReasonsPageController extends Controller
{
    public function __invoke(): View
    {
        // Per-country catalog — read on the active shard.
        return view('panel.cancellation-reasons.index', [
            'reasons' => CancellationReason::on(TenantConnection::current())
                ->orderBy('audience')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }
}
