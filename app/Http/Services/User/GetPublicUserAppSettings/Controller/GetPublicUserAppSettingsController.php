<?php
namespace App\Http\Services\User\GetPublicUserAppSettings\Controller;

use App\Http\Services\User\GetPublicUserAppSettings\Logic\GetPublicUserAppSettingsInput;
use App\Http\Services\User\GetPublicUserAppSettings\Logic\GetPublicUserAppSettingsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetPublicUserAppSettings\Request\GetPublicUserAppSettingsRequest;

class GetPublicUserAppSettingsController extends Controller
{
    public function __invoke(GetPublicUserAppSettingsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetPublicUserAppSettingsInput($request->validated());

        $service = new GetPublicUserAppSettingsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}