<?php

use App\Http\Controllers\PublicLegalController;
use App\Http\Controllers\SharedTripController;
use Illuminate\Support\Facades\Route;

Route::get('t/{slug}', SharedTripController::class)->name('public.shared-trip');

// Public legal copy (terms/privacy) for the apps + website. Additive; the apps
// may fetch it — it never changes their existing contract.
Route::get('content/legal', PublicLegalController::class)->name('public.legal');

// App maintenance + version gates, polled by the apps on launch.
Route::get('content/app-status', \App\Http\Controllers\PublicAppStatusController::class)->name('public.app-status');
