<?php
namespace App\Http\Services\User\GetReferralMessage\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetReferralMessageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetReferralMessageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // write your logic code..
        // $table->string('referralCode');//appLink
        $user = $this->repository->UserRepository()
        ->readRepository()->find(getAuthUser()->id);

        $playLink   = 'https://play.google.com/store/apps/details?id=com.codepac.fleetapp&pli=1';
        $iosLink    = 'https://play.google.com/store/apps/details?id=com.codepac.fleetapp&pli=1';


        $response  = new GetReferralMessageOutput([
            'referralCode' =>$user->referralCode ,
            'appLink'=>$playLink,
            'iosLink'=>$iosLink,
        ] , 'this is your referral details');
        return $response->send_as_object();
   }
}