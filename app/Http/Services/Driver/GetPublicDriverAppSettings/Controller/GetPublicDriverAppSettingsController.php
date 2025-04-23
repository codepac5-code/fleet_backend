<?php
namespace App\Http\Services\Driver\GetPublicDriverAppSettings\Controller;

use App\Http\Services\Driver\GetPublicDriverAppSettings\Logic\GetPublicDriverAppSettingsInput;
use App\Http\Services\Driver\GetPublicDriverAppSettings\Logic\GetPublicDriverAppSettingsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetPublicDriverAppSettings\Request\GetPublicDriverAppSettingsRequest;

class GetPublicDriverAppSettingsController extends Controller
{
    public function __invoke(GetPublicDriverAppSettingsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetPublicDriverAppSettingsInput($request->validated());

        $service = new GetPublicDriverAppSettingsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}