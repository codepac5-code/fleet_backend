<?php
namespace App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Controller;

use App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Logic\SaveTermAndConditionInput;
use App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Logic\SaveTermAndConditionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Settings\PublicSettingsKies;
use App\Http\Core\Const\Options\Settings\SettingsTypes;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Request\SaveTermAndConditionRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SaveTermAndConditionController extends Controller
{
    public function __invoke(SaveTermAndConditionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new SaveTermAndConditionInput($request->all());

        $service = new SaveTermAndConditionLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }




    public function getView(Request $request)
    {

        $setting_data = Setting::where('type', SettingsTypes::$PublicSettings )->where('key', PublicSettingsKies::$TermsCondition)->first();
        $pageTitle = __('messages.terms_condition');
        $assets = ['textarea'];
        return view('setting.term_condition_form', compact('setting_data', 'pageTitle', 'assets'));
    }
    



}