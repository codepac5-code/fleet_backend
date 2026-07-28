<?php

namespace App\Http\Services\Panel\Admin\Currencies\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Currencies\Request\StoreCurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;

class StoreCurrencyController extends Controller
{
    public function __invoke(StoreCurrencyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['decimals']      = $data['decimals'] ?? 2;
        $data['exchange_rate'] = $data['exchange_rate'] ?? 1;
        $data['is_default']    = (bool) ($data['is_default'] ?? false);
        $data['is_active']     = true;

        if ($data['is_default']) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        Currency::create($data);

        return redirect()->route('panel.admin.currencies.index');
    }
}
