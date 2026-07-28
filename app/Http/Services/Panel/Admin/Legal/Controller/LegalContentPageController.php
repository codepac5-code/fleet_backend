<?php

namespace App\Http\Services\Panel\Admin\Legal\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\View\View;

class LegalContentPageController extends Controller
{
    public function __invoke(): View
    {
        // Legal copy is platform-wide (global SiteSetting) — the same terms and
        // privacy text for every country, only the locale differs.
        return view('panel.legal.index', [
            'terms_en' => (string) SiteSetting::val('legal_terms_en', ''),
            'terms_ar' => (string) SiteSetting::val('legal_terms_ar', ''),
            'privacy_en' => (string) SiteSetting::val('legal_privacy_en', ''),
            'privacy_ar' => (string) SiteSetting::val('legal_privacy_ar', ''),
        ]);
    }
}
