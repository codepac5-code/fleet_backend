<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use Illuminate\Http\RedirectResponse;

class DeleteServiceController extends Controller
{
    public function __invoke(int $service, ServiceRepository $services): RedirectResponse
    {
        $services->delete($services->findOrFail($service));

        return redirect()
            ->route('panel.admin.service.index')
            ->with('status', textByLanguage('تم حذف الخدمة', 'Service deleted'));
    }
}
