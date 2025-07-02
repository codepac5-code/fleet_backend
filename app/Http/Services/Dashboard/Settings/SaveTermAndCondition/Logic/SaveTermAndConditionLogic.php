<?php
namespace App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Logic;

use App\Http\Core\Const\Options\Settings\PublicSettingsKies;
use App\Http\Core\Const\Options\Settings\SettingsTypes;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;

class SaveTermAndConditionLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SaveTermAndConditionInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // if (demoUserPermission()) {
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $value_ar = $this->input->getArValue();
        $value_en = $this->input->getEnValue();

        $setting_data = [
            'type'  => SettingsTypes::$PublicSettings,
            'key'   => PublicSettingsKies::$TermsCondition,
        ];


        $result = $this->repository->SettingRepository()
        ->readRepository()->getFirstByConditions($setting_data);

        if($result != null ){
            
            $result = $this->repository->SettingRepository()->updateRepository()
            ->update(['id'=>$result->id], ['value' =>  $value_ar , 'value_en'=>$value_en]);
        }else{
            $setting_data['value_en'] = $value_en;
            $setting_data['value']    = $value_ar;

            $result = $this->repository->SettingRepository()
            ->createRepository()->create($setting_data);
        }

        
        // if ($result->wasRecentlyCreated) {
        //     $message = __('messages.save_form', ['form' => __('messages.terms_condition')]);
        // } else {
            $message = __('messages.update_form', ['form' => __('messages.terms_condition')]);
        //}

        return redirect()->back()->withsuccess($message);
   }
}