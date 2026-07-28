<?php

namespace App\Http\Services\Panel\Shared\Wallet\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Wallet\WalletReveal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RevealWalletController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string']]);

        $user = $scope->user();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['wallet' => textByLanguage('كلمة المرور غير صحيحة', 'Incorrect password')]);
        }

        WalletReveal::reveal();

        return back();
    }
}
