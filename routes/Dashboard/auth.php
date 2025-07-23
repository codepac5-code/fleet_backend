<?php

use App\Http\Controllers\NotificationController;
use App\Http\Core\Const\Options\Settings\PublicSettingsKies;
use App\Http\Core\Const\Options\Settings\SettingsTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Services\Dashboard\Front\Controller\FrontController;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Controller\LoginToDashboardController;
use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Controller\LoginToDashboardAsOfficeController;
use App\Http\Services\WebSite\GetPrivacyPolicyPage\Controller\GetPrivacyPolicyPageController;
use App\Http\Services\WebSite\ViewFleetLandingPage\Controller\ViewFleetLandingPageController;
use App\Models\Office;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use Illuminate\Support\Facades\Artisan;





Route::group(['middleware' => ['set-localization']],function () {


Route::get('/ppp',function(){
    $conditions = [
        'type'  => SettingsTypes::$PublicSettings,
        'key'   => PublicSettingsKies::$TermsCondition,
    ];

    $select = select_by_language([
        'value',//'value_ar'
        'type',
        'key' , 
         ] , [
            'value'//'value_en'
            ,'type',
            'key' , 
    ]);


    $termsCondition = Setting::where($conditions)->first();
    
    


     $privacy_policy = $termsCondition->value;
      //$privacy_policy = 'sdsssssssssssssss';

    return response()->json(['pp'=>$privacy_policy]);

});


Route::get('/privacy-policy',GetPrivacyPolicyPageController::class)->name('privacy-policy');

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


Route::get('login-office',function(){
    // $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
    // $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
    $sectionData['description'] = 'Welcome To ';
    $sectionData['title'] = 'Welcome To Our Fleet';
    $sectionData['login_register'] = 1;
  
  
    return view('landing-page.login',compact('sectionData'));
  });

Route::post('/login-office-check', LoginToDashboardAsOfficeController::class )
->name('login-office-check');

//    return redirect()->back()->withErrors(['general' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']);

  

Route::get('/login-admin', function(){
    return view('auth.login');
})->name('login-admin')//->middleware('guest')
;


Route::post('/admin', LoginToDashboardController::class )
->name('admin.login');



Route::get('/login-page', [FrontController::class, 'userLoginView'])->name('user.login');


Route::get('delete-account',function(){
    return view('fleet-landing-page.deleteAccount');

})->name('delete-account');

Route::get('support',function(){
    return view('fleet-landing-page.support');

})->name('support-form');


Route::get('driver-join',function(){

    $brands = VehicleBrand::all();
    $offices = Office::all();

    return view('fleet-landing-page.driverJobApplication',compact('brands','offices'));

})->name('driver-join');













//--------------


Route::get('api/user-alerts', [NotificationController::class, 'getNotifications']);
Route::delete('api/user-alerts', [NotificationController::class, 'clearNotifications']);


// Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');

// Route::get('/tickets', [IssueController::class, 'getTickets']);
// Route::delete('/tickets/{id}', [IssueController::class, 'destroy']);


// Route::prefix('api/tickets')->group(function () {
//     Route::get('/', [IssueController::class, 'index']);  
//     Route::delete('/{id}', [IssueController::class, 'destroy']);

    
// });


// Route::get('/filters', [IssueController::class, 'filters']);
