<?php
namespace App\Http\Services\WebSite\GetPrivacyPolicyPage\Logic;

use App\Http\Core\Const\Options\Settings\PublicSettingsKies;
use App\Http\Core\Const\Options\Settings\SettingsTypes;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetPrivacyPolicyPageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetPrivacyPolicyPageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

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


        $termsCondition =$this->repository->SettingRepository()->readRepository()
        ->getFirstByConditions( $conditions ,$select);

        //  $privacy_policy = $termsCondition->value;


        $privacy_policy = "ddd";

        return view('fleet-landing-page.pp',compact( 'privacy_policy'));
  
   }
}