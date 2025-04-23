<?php
namespace App\Http\Services\Dashboard\Front\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class FrontLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private FrontInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new FrontOutput([] , '');
        return $response->send_as_array();
   }


   public function userLoginView(){
    
    $footerSection = $this->repository->FrontendSettingRepository()
    ->readRepository()->getByValue('key', 'login-register-setting');
    $sectionData = null; //$footerSection ? json_decode($footerSection->value, true) : null;
    return view('landing-page.login',compact('sectionData'));
}
}