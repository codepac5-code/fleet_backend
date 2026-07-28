<?php

namespace App\Http\Services\Panel\Shared\Locale;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\LanguageOptions;
use Illuminate\Http\RedirectResponse;

class SwitchLocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        if (in_array($locale, LanguageOptions::$language, true)) {
            session([
                'locale' => $locale,
                'dir'    => $locale === 'ar' ? 'rtl' : 'ltr',
            ]);

            app()->setLocale($locale);
        }

        return back();
    }
}
