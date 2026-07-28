<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Concerns\HandlesImageUpload;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Request\StoreServiceRequest;
use Illuminate\Http\RedirectResponse;

class StoreServiceController extends Controller
{
    use HandlesImageUpload;

    public function __invoke(StoreServiceRequest $request, ServiceRepository $services): RedirectResponse
    {
        $data = $request->payload();
        $data['image'] = $this->uploadedImage($request, 'services') ?? '';

        $services->create($data);

        return redirect()
            ->route('panel.admin.service.index')
            ->with('status', textByLanguage('تمت إضافة الخدمة بنجاح', 'Service created successfully'));
    }
}
