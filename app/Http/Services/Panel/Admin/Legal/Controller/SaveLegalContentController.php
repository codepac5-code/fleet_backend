<?php

namespace App\Http\Services\Panel\Admin\Legal\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveLegalContentController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'terms_en' => ['nullable', 'string', 'max:50000'],
            'terms_ar' => ['nullable', 'string', 'max:50000'],
            'privacy_en' => ['nullable', 'string', 'max:50000'],
            'privacy_ar' => ['nullable', 'string', 'max:50000'],
        ]);

        foreach (['terms_en', 'terms_ar', 'privacy_en', 'privacy_ar'] as $key) {
            SiteSetting::put('legal_' . $key, (string) ($data[$key] ?? ''));
        }

        if (method_exists(SiteSetting::class, 'flush')) {
            SiteSetting::flush();
        }

        return redirect()
            ->route('panel.admin.legal.index')
            ->with('status', textByLanguage('تم حفظ المحتوى القانوني', 'Legal content saved'));
    }
}
