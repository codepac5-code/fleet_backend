<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Offices\Request\UpdateOfficeRequest;
use Illuminate\Http\RedirectResponse;

class UpdateOfficeController extends Controller
{
    public function __invoke(UpdateOfficeRequest $request, int $office, OfficeRepository $offices): RedirectResponse
    {
        $model = $offices->findOrFail($office);

        $data = $request->validated();
        $data['limitOrders'] = (int) ($data['limitOrders'] ?? 0);

        $offices->update($model, $data);

        return redirect()
            ->route('panel.admin.office.index')
            ->with('status', textByLanguage('تم تحديث المكتب بنجاح', 'Office updated successfully'));
    }
}
