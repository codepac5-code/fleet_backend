<?php

use Illuminate\Support\Facades\Route;
use App\Http\Services\Panel\Auth\Controller\LoginPageController;
use App\Http\Services\Panel\Auth\Controller\LoginController;
use App\Http\Services\Panel\Auth\Controller\LogoutController;
use App\Http\Services\Panel\Shared\Locale\SwitchLocaleController;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;

Route::middleware('set-language')->group(function () {
    Route::get('locale/{locale}', SwitchLocaleController::class)->whereIn('locale', ['ar', 'en'])->name('locale');
    Route::get('login',  LoginPageController::class)->name('login');
    Route::post('login', LoginController::class)->name('login.attempt');
    Route::get('two-factor', \App\Http\Services\Panel\Auth\Controller\TwoFactorChallengePageController::class)->name('two-factor.challenge');
    Route::post('two-factor', \App\Http\Services\Panel\Auth\Controller\VerifyTwoFactorController::class)->name('two-factor.verify');
    Route::post('logout', LogoutController::class)->name('logout');

    // Signing out is a POST so no third-party page can end a session with an
    // <img> tag. A plain visit to the URL — a bookmark, a Back after signing
    // out, a typed address — used to answer "405 method not supported", which
    // reads as a broken panel. It now simply goes where the visitor meant.
    Route::get('logout', function () {
        $guard = app(EntityScope::class)->guard();

        return $guard === null
            ? redirect()->route('panel.login')
            : redirect()->route("panel.{$guard}.home");
    })->name('logout.visit');
});
