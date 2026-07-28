<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use Illuminate\Http\RedirectResponse;

class ToggleDriverStatusController extends Controller
{
    public function __invoke(int $driver, DriverRepository $drivers): RedirectResponse
    {
        $drivers->toggleStatus($drivers->findOrFail($driver));

        return back();
    }
}
