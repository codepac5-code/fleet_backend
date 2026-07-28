<?php

namespace App\Http\Services\Panel\Tariffs\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Pricing\TariffService;
use App\Http\Core\Const\Options\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DeleteOfficeTariffController extends Controller
{
    public function __invoke(string $serviceClass, TariffService $tariffs): RedirectResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();
        $tariffs->remove($officeId, $serviceClass);

        return back()->with('status', textByLanguage('تم حذف التعرفة.', 'Tariff deleted.'));
    }
}
