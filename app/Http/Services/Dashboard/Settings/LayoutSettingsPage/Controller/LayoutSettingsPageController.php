<?php
namespace App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Controller;

use App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Logic\LayoutSettingsPageInput;
use App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Logic\LayoutSettingsPageLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Request\LayoutSettingsPageRequest;

class LayoutSettingsPageController extends Controller
{
    public function __invoke(LayoutSettingsPageRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new LayoutSettingsPageInput($request->validated());

        $service = new LayoutSettingsPageLogic($input); // call the service's logic

        // execute service and get result..
        return $service->{$request->page}(); // send response..

       // return SendResponse::sendSuccessResponse($result); 
    }
}