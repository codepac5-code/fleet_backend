<?php

use App\Http\Services\Dashboard\ServiceManagement\AddService\Controller\AddServiceController;
use App\Http\Services\Dashboard\ServiceManagement\DeleteService\Controller\DeleteServiceController;
use App\Http\Services\Dashboard\ServiceManagement\EditeService\Controller\EditeServiceController;
use App\Http\Services\Dashboard\ServiceManagement\ShowService\Controller\ShowServiceController;
use App\Http\Services\Dashboard\ServiceManagement\ViewService\Controller\ViewServiceController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   $user = User::where('id', 1)->first();
   return view('service.index' , compact('user'));
})->name('h');


// Route::group(['prefix' => 'service' ,'middleware' => ['auth:user']], function () {
//     Route::get('/view',ViewServiceController::class)->name('service.view');
//     Route::post('/add', AddServiceController::class)->name('service.create');
//     Route::post('/edite', EditeServiceController::class)->name('service.update');
//     Route::delete('/delete', DeleteServiceController::class)->name('service.delete');
//     Route::get('/show',ShowServiceController::class)->name('service.show');
//     Route::post('/show',ShowServiceController::class)->name('service.bulk-action');

// });


