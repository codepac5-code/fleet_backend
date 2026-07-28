<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use Illuminate\Http\RedirectResponse;

class ToggleOfficeStatusController extends Controller
{
    public function __invoke(int $office, OfficeRepository $offices): RedirectResponse
    {
        $offices->toggleStatus($offices->findOrFail($office));

        return back();
    }
}
