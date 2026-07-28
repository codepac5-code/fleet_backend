<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use Illuminate\Http\RedirectResponse;

class DeleteSubServiceController extends Controller
{
    public function __invoke(int $service, int $subService, SubServiceRepository $subServices): RedirectResponse
    {
        $subServices->delete($subServices->findForService($service, $subService));

        return redirect()
            ->route('panel.admin.service.sub.index', $service)
            ->with('status', textByLanguage('تم حذف الخدمة الفرعية', 'Sub-service deleted'));
    }
}
