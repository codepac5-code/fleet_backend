<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use Illuminate\Http\RedirectResponse;

class ToggleServiceController extends Controller
{
    public function __invoke(int $service, ServiceRepository $services): RedirectResponse
    {
        $services->toggle($services->findOrFail($service));

        return back();
    }
}
