<?php
namespace App\Http\Services\SharedServices;

use Illuminate\Http\Request;
use App\Http\Response\SendResponse;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class SwitchLanguageController extends Controller
{
    public function __invoke(Request $request , $locale)
    {

        //App::setLocale($locale);
        // $locale = 'ar';
        // return $locale;
        app()->setLocale($locale); 
        session()->put('locale', $locale);
        Artisan::call('cache:clear');
        $dir = 'ltr';
        if (in_array($locale, ['ar', 'dv', 'ff', 'ur', 'he', 'ku', 'fa'])) {
            $dir = 'rtl';
        }
        info('Locale switched to: ' . $locale);


        session()->put('dir',  $dir);
        if (auth()->check()) {
            $user = auth()->user();
            $user->language_option = $locale;
            $user->save();
        }
        return redirect()->back();
            

        // $dir = 'ltr';
        
        //      $availableLanguages = ['ar', 'dv', 'ff', 'ur', 'he', 'ku', 'fa']; 
        //      if (in_array($locale, $availableLanguages)) {
        //         $dir = 'rtl';
        //          App::setLocale($locale);
        //          Session::put('locale', $locale);
                 
        //      }
        //     session()->put('dir',  $dir);

             // if (auth()->check()) {
             //     $user = auth()->user();
             //     $user->language_option = $locale;
             //     $user->save();
             // }
            //  return redirect()->back();
    }



    // public function lang($locale)
    // {
    //     \App::setLocale($locale);
    //     session()->put('locale', $locale);
    //     \Artisan::call('cache:clear');
    //     $dir = 'ltr';
    //     if (in_array($locale, ['ar', 'dv', 'ff', 'ur', 'he', 'ku', 'fa'])) {
    //         $dir = 'rtl';
    //     }

    //     session()->put('dir',  $dir);
    //     if (auth()->check()) {
    //         $user = auth()->user();
    //         $user->language_option = $locale;
    //         $user->save();
    //     }
    //     return redirect()->back();
    // }
}