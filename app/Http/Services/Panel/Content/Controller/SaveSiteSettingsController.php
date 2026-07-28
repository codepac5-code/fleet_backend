<?php

namespace App\Http\Services\Panel\Content\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Content\SiteSettingsSchema;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveSiteSettingsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(SiteSettingsSchema::rules());

        foreach (SiteSettingsSchema::allKeys() as $key) {
            if ($key === 'brand_logo') {
                continue;
            }

            if ($request->has($key)) {
                SiteSetting::put($key, (string) $request->input($key));
            }
        }

        if ($request->hasFile('brand_logo')) {
            SiteSetting::put('brand_logo', $request->file('brand_logo')->store('site', 'public'));
        }

        if (method_exists(SiteSetting::class, 'flush')) {
            SiteSetting::flush();
        }

        return back()->with('status', textByLanguage('تم حفظ الإعدادات.', 'Settings saved.'));
    }
}
