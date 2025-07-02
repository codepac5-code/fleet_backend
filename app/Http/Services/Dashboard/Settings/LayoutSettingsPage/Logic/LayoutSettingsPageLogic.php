<?php
namespace App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Logic;

use App\Http\Core\Const\Options\Settings\SettingsTypes;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\City;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\Setting;

class LayoutSettingsPageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LayoutSettingsPageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // write your logic code..

        $response  = new LayoutSettingsPageOutput([] , '');
        return $response->send_as_array();
   }

   public function social(){

   return view('setting.public-setting.social');
   }




   public function general_setting(){
    $generalsetting   = Setting::where('type', '=', 'general-setting')->first();

    $countries = Country::pluck('name', 'id');
    $cities = City::pluck('name', 'id');

    if (!empty($generalsetting['value'])) {
        $decodedata = json_decode($generalsetting['value']);
        $keys = ['site_name', 'site_description', 'inquriy_email', 'helpline_number', 'website', 'country', 'region', 'city', 'address'];
        foreach ($keys as $key) {
            $generalsetting[$key] = $decodedata->$key;
        }
    }
    $page = 'general setting';
    return view('setting.general_setting', compact('page', 'generalsetting','cities','countries'));
    
   }

   public function payment_methods_settings(){
    $paymentMethods = PaymentMethod::all();

    
    return view('setting.public-setting.payment_settings', compact('paymentMethods'));
    
   }

   public function web_site_settings(){


    $settings = [
        'HomeTitle'            => 'مرحبا بكم في شركتنا',
        'HomeSubTitle'         => 'نحن نقدم أفضل الحلول',
        'HomeContent'          => 'نحن شركة متخصصة في تقديم خدمات تقنية عالية الجودة لعملائنا حول العالم. رؤيتنا هي توفير حلول مبتكرة ومستدامة.',
        'HomePhoto'            => 'uploads/home_photo.jpg',
    
        'SliderTitleBlack'     => 'ابتكار',
        'SliderTitleYellow'    => 'مستقبل',
    
        'FirstSliderTitle'     => 'حلول ذكية',
        'FirstSliderContent'   => 'نحن ندمج التكنولوجيا مع الإبداع لنقدم لك حلولاً تفوق التوقعات.',
        'FirstSliderImage'     => 'uploads/slider1.jpg',
    
        'SecondSliderTitle'    => 'خبرة متميزة',
        'SecondSliderContent'  => 'فريقنا يضم خبراء في مجالات متنوعة لضمان أفضل نتائج.',
        'SecondSliderImage'    => 'uploads/slider2.jpg',
    
        'ThirdSliderTitle'     => 'دعم متواصل',
        'ThirdSliderContent'   => 'نحن معك في كل خطوة، من البداية وحتى تحقيق النجاح.',
        'ThirdSliderImage'     => 'uploads/slider3.jpg',
    ];

    return view('setting.public-setting.website', [
        'settings' => $settings,
    ]);
    }


    
    public function role_permission_setup(){
            $tabpage = 'role';
            $page = 'role permission setup';
           return view('setting.role-permission-setup', compact('page', 'tabpage'))->render();
    }
    // $select = select_by_language([
    //     'value',//'value_ar'
    //     'type',
    //     'key' , 
    //      ] , [
    //         'value_en as value'
    //         ,'type',
    //         'key' , 
    // ]);

    // $web_settings = $this->repository->SettingRepository()
    // ->readRepository()
    // ->getFirstByConditions(['type'=>SettingsTypes::$WebSite , 'key'=>SettingsTypes::$WebSite] , ['type','key','value']);
    
    // $settings = json_decode($web_settings->value);

    // return view('settings.web_site_settings')->compact(['settings']);
   
}