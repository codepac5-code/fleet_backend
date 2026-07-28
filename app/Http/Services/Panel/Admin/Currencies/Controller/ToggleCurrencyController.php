<?php

namespace App\Http\Services\Panel\Admin\Currencies\Controller;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;

class ToggleCurrencyController extends Controller
{
    public function __invoke(Currency $currency): RedirectResponse
    {
        if ($currency->is_default && $currency->is_active) {
            return back()->withErrors(['currency' => __('messages.cannot_disable_default_currency')]);
        }

        $currency->update(['is_active' => ! $currency->is_active]);

        return back();
    }
}
