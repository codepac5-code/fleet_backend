<?php

namespace App\Http\Services\Panel\Content\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Content\SiteSettingsSchema;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;

class SiteSettingsPageController extends Controller
{
    public function __invoke(): View
    {
        $values = [];

        foreach (SiteSettingsSchema::allKeys() as $key) {
            $values[$key] = SiteSetting::val($key);
        }

        return view('panel.site-settings.index', [
            'entity' => 'admin',
            'groups' => SiteSettingsSchema::GROUPS,
            'values' => $values,
        ]);
    }
}
