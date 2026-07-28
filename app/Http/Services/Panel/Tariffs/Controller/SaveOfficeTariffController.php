<?php

namespace App\Http\Services\Panel\Tariffs\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffService;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaveOfficeTariffController extends Controller
{
    public function __invoke(Request $request, TariffService $tariffs): RedirectResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();
        $serviceClass = trim((string) $request->input('service_class', ''));
        $style = (string) $request->input('pricing_style', PricingService::STYLE_METER);

        if ($serviceClass === '') {
            return back()->with('error', textByLanguage('فئة الخدمة مطلوبة.', 'Service class is required.'));
        }

        if (! in_array($style, [PricingService::STYLE_METER, PricingService::STYLE_FIXED], true)) {
            return back()->with('error', textByLanguage('نمط التسعير يجب أن يكون عدّاد أو ثابت.', 'Pricing style must be meter or fixed.'));
        }

        $currency = strtoupper((string) ($request->input('currency_code') ?: ShardManager::currency()));

        // The form collects WHOLE currency (8000.50); storage is minor units.
        // Doing this conversion here — rather than trusting whatever number the
        // field happened to contain — is what stops an office typing 8000 and
        // silently pricing every ride at 80.00. `*_minor` inputs are still
        // accepted so any older form or API caller keeps working unchanged.
        $tariffs->upsertForOffice($officeId, $serviceClass, $currency, $style, [
            'base_minor' => TariffService::toMinor($request->input('base_amount'), $request->input('base_minor')),
            'per_km_minor' => TariffService::toMinor($request->input('per_km_amount'), $request->input('per_km_minor')),
            'per_minute_minor' => TariffService::toMinor($request->input('per_minute_amount'), $request->input('per_minute_minor')),
            'minimum_minor' => TariffService::toMinor($request->input('minimum_amount'), $request->input('minimum_minor')),
            'fixed_minor' => TariffService::toMinor($request->input('fixed_amount'), $request->input('fixed_minor')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', textByLanguage('تم حفظ التعرفة.', 'Tariff saved.'));
    }
}
