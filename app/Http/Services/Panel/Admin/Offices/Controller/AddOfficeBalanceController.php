<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeWalletService;
use App\Http\Services\Panel\Admin\Offices\Request\AddBalanceRequest;
use Illuminate\Http\RedirectResponse;

class AddOfficeBalanceController extends Controller
{
    public function __invoke(AddBalanceRequest $request, int $office, OfficeRepository $offices, OfficeWalletService $wallet): RedirectResponse
    {
        $model = $offices->findOrFail($office);

        $wallet->addBalance($model, (float) $request->validated()['amount'], $request->input('note'));

        return redirect()
            ->route('panel.admin.office.show', $model->id)
            ->with('status', textByLanguage('تمت إضافة الرصيد بنجاح', 'Balance added successfully'));
    }
}
