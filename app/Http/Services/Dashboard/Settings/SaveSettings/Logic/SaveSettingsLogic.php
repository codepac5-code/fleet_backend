<?php
namespace App\Http\Services\Dashboard\Settings\SaveSettings\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class SaveSettingsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SaveSettingsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // write your logic code..

        $response  = new SaveSettingsOutput([] , '');
        return $response->send_as_array();
   }

   public function web_site_settings(){

    $request = $this->input->getRequest();
    $condition = [
        'type'  =>  $request['type'],
        'key'   =>  $request['key'],
    ];

    if( isset($request['id']) ){
        $condition['id'] = $request['id'];
    }

    $value = [
        ''=>''
    ];

    $this->repository->SettingRepository()
    ->updateRepository()
    ->update($condition ,['value'=>json_encode($value)]);

    



    //     $settings = $this->repository->SettingRepository()
    //     ->updateRepository()->update();
    }

 }