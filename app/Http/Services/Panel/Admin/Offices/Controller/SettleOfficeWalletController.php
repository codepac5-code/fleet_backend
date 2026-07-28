<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeWalletService;
use App\Http\Services\Panel\Admin\Offices\Request\SettleWalletRequest;
use Illuminate\Http\RedirectResponse;

class SettleOfficeWalletController extends Controller
{
    public function __invoke(SettleWalletRequest $request, int $office, OfficeRepository $offices, OfficeWalletService $wallet): RedirectResponse
    {
        $model = $offices->findOrFail($office);

        $applied = $wallet->settle($model, (float) $request->validated()['amount'], $request->input('note'));

        return redirect()
            ->route('panel.admin.office.show', $model->id)
            ->with('status', textByLanguage('تمت تسوية ', 'Settled ') . getPriceFormat($applied));
    }
}
