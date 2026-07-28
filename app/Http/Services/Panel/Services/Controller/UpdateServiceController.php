<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Concerns\HandlesImageUpload;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Request\UpdateServiceRequest;
use Illuminate\Http\RedirectResponse;

class UpdateServiceController extends Controller
{
    use HandlesImageUpload;

    public function __invoke(UpdateServiceRequest $request, int $service, ServiceRepository $services): RedirectResponse
    {
        $model = $services->findOrFail($service);

        $data = $request->payload();
        if ($image = $this->uploadedImage($request, 'services')) {
            $data['image'] = $image;
        }

        $services->update($model, $data);

        return redirect()
            ->route('panel.admin.service.index')
            ->with('status', textByLanguage('تم تحديث الخدمة بنجاح', 'Service updated successfully'));
    }
}
