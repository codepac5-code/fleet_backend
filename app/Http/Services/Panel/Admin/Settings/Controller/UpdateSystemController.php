<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Settings\Logic\SettingsRepository;
use App\Http\Services\Panel\Admin\Settings\Request\UpdateSystemRequest;
use Illuminate\Http\RedirectResponse;

class UpdateSystemController extends Controller
{
    public function __invoke(UpdateSystemRequest $request, SettingsRepository $settings): RedirectResponse
    {
        $settings->updateSystem($request->validated());

        return redirect()
            ->route('panel.admin.settings.index')
            ->with('status', textByLanguage('تم تحديث إعدادات النظام', 'System settings updated'));
    }
}
