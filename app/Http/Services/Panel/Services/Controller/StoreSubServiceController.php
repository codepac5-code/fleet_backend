<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Concerns\HandlesImageUpload;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use App\Http\Services\Panel\Services\Request\StoreSubServiceRequest;
use Illuminate\Http\RedirectResponse;

class StoreSubServiceController extends Controller
{
    use HandlesImageUpload;

    public function __invoke(StoreSubServiceRequest $request, int $service, ServiceRepository $services, SubServiceRepository $subServices): RedirectResponse
    {
        $model = $services->findOrFail($service);

        $data = $request->payload();
        $data['serviceId'] = $model->id;
        if ($image = $this->uploadedImage($request, 'sub-services')) {
            $data['image'] = $image;
        }

        $subServices->create($data);

        return redirect()
            ->route('panel.admin.service.sub.index', $model->id)
            ->with('status', textByLanguage('تمت إضافة الخدمة الفرعية بنجاح', 'Sub-service created successfully'));
    }
}
