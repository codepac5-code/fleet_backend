<?php

namespace App\Http\Services\Panel\Admin\Currencies\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Currencies\Request\UpdateCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;

class UpdateCurrencyController extends Controller
{
    public function __invoke(UpdateCurrencyRequest $request, Currency $currency): RedirectResponse
    {
        $data = $request->validated();
        $data['decimals']   = $data['decimals'] ?? $currency->decimals;
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($currency->is_default) {
            $data['is_default']    = true;
            $data['exchange_rate'] = 1;
        }

        if ($data['is_default'] && ! $currency->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($data);

        return redirect()->route('panel.admin.currencies.index');
    }
}
