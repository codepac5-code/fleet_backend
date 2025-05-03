<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Services\Dashboard\Front\Controller\FrontController;
use App\Http\Services\Dashboard\Auth\LoginToDashboard\Controller\LoginToDashboardController;
use App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Controller\LoginToDashboardAsOfficeController;

Route::get('/',function(){
    // $footerSection = FrontendSetting::where('key', 'login-register-setting')->first();
    // $sectionData = $footerSection ? json_decode($footerSection->value, true) : null;
    $sectionData['description'] = 'Here, you can efficiently monitor and manage your fleet\'s activities, track vehicle status, view ride history, and oversee driver performance.';
    $sectionData['title'] = "Welcome! Ready to Take Control of Your Fleet?";
    $sectionData['login_register'] = 1;
  
    return view('TT.index',compact('sectionData'));

return view('landing-page.login',compact('sectionData'));
})->name('login');


Route::post('/login-office', LoginToDashboardAsOfficeController::class )
->name('login-office');
  

Route::get('/login-admin', function(){
    return view('auth.login');
})->name('login-admin')//->middleware('guest')
;


Route::post('/admin', LoginToDashboardController::class )
->name('admin.login');



Route::get('/login-page', [FrontController::class, 'userLoginView'])->name('user.login');
