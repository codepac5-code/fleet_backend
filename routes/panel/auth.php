<?php

use Illuminate\Support\Facades\Route;
use App\Http\Services\Panel\Auth\Controller\LoginPageController;
use App\Http\Services\Panel\Auth\Controller\LoginController;
use App\Http\Services\Panel\Auth\Controller\LogoutController;
use App\Http\Services\Panel\Shared\Locale\SwitchLocaleController;

Route::middleware('set-language')->group(function () {
    Route::get('locale/{locale}', SwitchLocaleController::class)->whereIn('locale', ['ar', 'en'])->name('locale');
    Route::get('login',  LoginPageController::class)->name('login');
    Route::post('login', LoginController::class)->name('login.attempt');
    Route::post('logout', LogoutController::class)->name('logout');
});
