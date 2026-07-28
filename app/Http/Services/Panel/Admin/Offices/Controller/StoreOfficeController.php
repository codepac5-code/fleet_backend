<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Offices\Request\StoreOfficeRequest;
use Illuminate\Http\RedirectResponse;

class StoreOfficeController extends Controller
{
    public function __invoke(StoreOfficeRequest $request, OfficeRepository $offices): RedirectResponse
    {
        $data = $request->validated();
        $data['limitOrders'] = (int) ($data['limitOrders'] ?? 0);

        $offices->create($data);

        return redirect()
            ->route('panel.admin.office.index')
            ->with('status', textByLanguage('تمت إضافة المكتب بنجاح', 'Office created successfully'));
    }
}
