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
use App\Models\Booking;
use App\Models\Office;
use App\Models\Setting;
use App\Models\VehicleBrand;
use App\Services\StripeService;
use Stripe\PaymentIntent;
use Stripe\Stripe;

Route::get('/strip/test',function(){
    $booking = Booking::find(657);
    return view('stripe_test',compact('booking'));
});


// Route::get('/checkout/{booking}', [PaymentController::class, 'checkout'])->name('checkout');


Route::post('/payment',function(Request $request){
    $request->validate([
        'payment_method' => 'required|string',
        'booking_id' => 'required|exists:bookings,id',
    ]);

    $booking = Booking::findOrFail($request->booking_id);

    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        // Create PaymentIntent if not exists
        if (!$booking->stripe_payment_intent_id) {
            $intent = PaymentIntent::create([
                'amount' => $booking->totalAmount * 100, // in cents
                'currency' => 'usd',
                'payment_method' => $request->payment_method,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            $booking->stripe_payment_intent_id = $intent->id;
            $booking->paymentStatus = $intent->status;
            $booking->save();
        } else {
            // Retrieve existing intent
            $intent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);
            $intent->confirm();
            $booking->paymentStatus = $intent->status;
            $booking->save();
        }

        return response()->json([
            'status' => $intent->status,
            'intent_id' => $intent->id
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'failed',
            'error' => $e->getMessage()
        ], 400);
    }
})->name('payments.process');


Route::get('/font',function(){
    return view('font');
});


Route::group(['middleware' => ['set-localization']],function () {

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');
    
Route::get('/ppp',function(){
    $conditions = [
        'type'  => SettingsTypes::$PublicSettings,
        'key'   => PublicSettingsKies::$TermsCondition,
    ];

    $select = select_by_language([
        'value', //'value_ar'
        'type',
        'key' , 
         ] , [
            'value' //'value_en'
            ,'type',
            'key' , 
    ]);


    $termsCondition = Setting::where($conditions)->first();
    
    $privacy_policy = $termsCondition->value;
    // $privacy_policy = 'sdsssssssssssssss';
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
  })->name('login.office');

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
