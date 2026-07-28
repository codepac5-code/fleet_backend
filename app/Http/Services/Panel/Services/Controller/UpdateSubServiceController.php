<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Concerns\HandlesImageUpload;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use App\Http\Services\Panel\Services\Request\UpdateSubServiceRequest;
use Illuminate\Http\RedirectResponse;

class UpdateSubServiceController extends Controller
{
    use HandlesImageUpload;

    public function __invoke(UpdateSubServiceRequest $request, int $service, int $subService, ServiceRepository $services, SubServiceRepository $subServices): RedirectResponse
    {
        $model = $services->findOrFail($service);
        $sub = $subServices->findForService($model->id, $subService);

        $data = $request->payload();
        if ($image = $this->uploadedImage($request, 'sub-services')) {
            $data['image'] = $image;
        }

        $subServices->update($sub, $data);

        return redirect()
            ->route('panel.admin.service.sub.index', $model->id)
            ->with('status', textByLanguage('تم تحديث الخدمة الفرعية بنجاح', 'Sub-service updated successfully'));
    }
}
