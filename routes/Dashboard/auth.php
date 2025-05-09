<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Services\Dashboard\Front\Controller\FrontController;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Controller\LoginToDashboardController;
use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Controller\LoginToDashboardAsOfficeController;
use App\Http\Services\WebSite\ViewFleetLandingPage\Controller\ViewFleetLandingPageController;
use Illuminate\Support\Facades\Artisan;

Route::group(['middleware' => ['set-localization']],function () {

Route::get('/',ViewFleetLandingPageController::class)->name('login');

// Route::get('/lang-switch', function (Request $request) {
//     $locale = $request->input('lang');
//      return  switch_language_of_view_and_redirect_back($locale);
// })->name('lang.switch');
});
//    return view('fleet-landing-page.index',compact('sectionData'));

Route::get('/lang-switch', function (Request $request) {
    $locale = $request->input('lang');
    // if (!in_array($locale, ['en', 'ar'])) {
    //     abort(400);
    // }
    // session(['locale' => $locale]);
    // app()->setLocale($locale);
    // return redirect()->back();

     return  switch_language_of_view_and_redirect_back($locale);
})->name('lang.switch');


Route::post('/login-office', LoginToDashboardAsOfficeController::class )
->name('login-office');
  

Route::get('/login-admin', function(){
    return view('auth.login');
})->name('login-admin')//->middleware('guest')
;


Route::post('/admin', LoginToDashboardController::class )
->name('admin.login');



Route::get('/login-page', [FrontController::class, 'userLoginView'])->name('user.login');
