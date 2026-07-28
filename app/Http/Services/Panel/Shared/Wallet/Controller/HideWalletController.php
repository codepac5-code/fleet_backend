<?php

namespace App\Http\Services\Panel\Shared\Wallet\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Wallet\WalletReveal;
use Illuminate\Http\RedirectResponse;

class HideWalletController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        WalletReveal::hide();

        return back();
    }
}
