<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Settings\Logic\SettingsRepository;
use App\Http\Services\Panel\Admin\Settings\Request\UpdateCommissionsRequest;
use Illuminate\Http\RedirectResponse;

class UpdateCommissionsController extends Controller
{
    public function __invoke(UpdateCommissionsRequest $request, SettingsRepository $settings): RedirectResponse
    {
        $settings->updateCommissions($request->validated());

        return redirect()
            ->route('panel.admin.settings.index')
            ->with('status', textByLanguage('تم تحديث العمولات الافتراضية', 'Default commissions updated'));
    }
}
