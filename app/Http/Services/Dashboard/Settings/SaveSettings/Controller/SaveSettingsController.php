<?php
namespace App\Http\Services\Dashboard\Settings\SaveSettings\Controller;

use App\Http\Services\Dashboard\Settings\SaveSettings\Logic\SaveSettingsInput;
use App\Http\Services\Dashboard\Settings\SaveSettings\Logic\SaveSettingsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Settings\SaveSettings\Request\SaveSettingsRequest;

class SaveSettingsController extends Controller
{
    public function __invoke(SaveSettingsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new SaveSettingsInput($request->all());

        $service = new SaveSettingsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}