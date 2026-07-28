<?php

namespace App\Http\Services\Panel\Admin\Currencies\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Currency;
use Illuminate\Contracts\View\View;

class CurrenciesPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.currencies.index', [
            'entity'     => $scope->guard(),
            'user'       => $scope->user(),
            'currencies' => Currency::orderByDesc('is_default')->orderBy('code')->get(),
        ]);
    }
}
