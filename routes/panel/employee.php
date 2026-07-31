<?php

use Illuminate\Support\Facades\Route;
use App\Http\Services\Panel\Home\Controller\HomeController;
use App\Http\Services\Panel\Home\Controller\HomeStatsController;
use App\Http\Services\Panel\Home\Controller\HomeLiveController;
use App\Http\Services\Panel\Home\Controller\MapDriversController;
use App\Http\Services\Panel\Shared\Wallet\Controller\RevealWalletController;
use App\Http\Services\Panel\Shared\Wallet\Controller\HideWalletController;
use App\Http\Services\Panel\Shared\Notifications\Controller\NotificationsPageController;
use App\Http\Services\Panel\Shared\Notifications\Controller\MarkNotificationsReadController;

Route::middleware(['set-language', 'panel.country-db', 'auth:employee', 'panel.2fa'])
    ->group(function () {

        Route::get('/', HomeController::class)->name('home');
        Route::get('home/stats', HomeStatsController::class)->name('home.stats');
        Route::get('home/live', HomeLiveController::class)->name('home.live');
        Route::get('map/drivers', MapDriversController::class)->name('map.drivers');

        Route::get('notifications', NotificationsPageController::class)->name('notifications.index');
        Route::post('notifications/read', MarkNotificationsReadController::class)->name('notifications.read');

        Route::post('wallet/reveal', RevealWalletController::class)->name('wallet.reveal');
        Route::post('wallet/hide', HideWalletController::class)->name('wallet.hide');

        Route::get('security', \App\Http\Services\Panel\Security\Controller\SecurityPageController::class)->name('security.index');
        Route::post('security/two-factor', \App\Http\Services\Panel\Security\Controller\StartTwoFactorController::class)->name('security.two-factor.start');
        Route::post('security/two-factor/confirm', \App\Http\Services\Panel\Security\Controller\ConfirmTwoFactorController::class)->name('security.two-factor.confirm');
        Route::post('security/two-factor/disable', \App\Http\Services\Panel\Security\Controller\DisableTwoFactorController::class)->name('security.two-factor.disable');

    });
