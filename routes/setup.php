<?php

use App\Http\Services\Setup\Controller\CreateAdminController;
use App\Http\Services\Setup\Controller\CreateCountryController;
use App\Http\Services\Setup\Controller\FinishController;
use App\Http\Services\Setup\Controller\SaveDatabaseController;
use App\Http\Services\Setup\Controller\SetupPageController;
use App\Http\Services\Setup\Controller\TestDbController;
use Illuminate\Support\Facades\Route;

Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', SetupPageController::class)->name('index');
    Route::post('test-db', TestDbController::class)->name('test');
    Route::post('database', SaveDatabaseController::class)->name('database');
    Route::post('admin', CreateAdminController::class)->name('admin');
    Route::post('country', CreateCountryController::class)->name('country');
    Route::post('finish', FinishController::class)->name('finish');
});
