<?php
namespace App\Http\Services\Driver\GetPublicDriverAppSettings\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetPublicDriverAppSettingsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetPublicDriverAppSettingsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $syria_ar = [
            'country_name' => 'سوريا',
            'search_country' => 'sy',
            'continent' => 'آسيا',
            'currency_name' => 'الليرة السورية',
            'currency_symbol' => 'ل.س',
            // 'currency_subunit' => 'لا يوجد',
            'unit' => 'ل.س',
            'symbol' => 'ل.س',
            // 'phone_format' => '+963 9## ### ###',
            // 'country_code' => 'SY',
            // 'calling_code' => '+963',
            // 'timezone' => 'Asia/Damascus',
            // 'flag' => 'https://flagcdn.com/sy.svg',
            // 'latitude' => 33.5138,
            // 'longitude' => 36.2765,
            // 'currency_decimals' => 0,
            // 'is_active' => true,
            // 'iban_supported' => false,
            // 'swift_supported' => false,

        ];

        
        $syria_en = [
            'country_name' => 'Syria',
            'search_country' => 'sy',
            'continent' => 'Asia',
            'currency_name' => 'Syrian Pound',
            'currency_symbol' => 'SYP',
            // 'currency_subunit' => 'None',
            'unit' => 'SYP',
            'symbol' => 'SYP',
            // 'phone_format' => '+963 9## ### ###',
            // 'country_code' => 'SY',
            // 'calling_code' => '+963',
            // 'timezone' => 'Asia/Damascus',
            // 'flag' => 'https://flagcdn.com/sy.svg',
            // 'latitude' => 33.5138,
            // 'longitude' => 36.2765,
            // 'currency_decimals' => 0,
            // 'is_active' => true,
            // 'iban_supported' => false,
            // 'swift_supported' => false,
        ];

        
        
        $this->repository->PublicUserAppSettingRepository()->createRepository()
        ->create([
            'type'=>'public_settings' ,
            'name'=>'country_settings',
            'key' =>'country',
            'ar_value'=> json_encode($syria_ar) ,
            'en_value'=> json_encode($syria_en)
        ]);

        
        if(app()->getLocale() == 'ar'){
            $select = [
                'type',
                'name',
                'key', 
                'ar_value as value'
            ];
        }else{
            $select = [
                'type',
                'name',
                'key', 
                'en_value as value'
            ];
        }


        $settings = $this->repository->PublicUserAppSettingRepository()
        ->readRepository()
        ->getFirstByConditions([
            'type'=>'public_settings' ,
            'name'=>'country_settings',
            'key' =>'country',
        ],$select);

        $public_settings = [
            'country_settings' => json_decode($settings->value) 
        ];

        $response  = new GetPublicDriverAppSettingsOutput( $public_settings , 'get public driver app settings');
        return $response->send_as_object();
   }
}