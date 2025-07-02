<?php
namespace App\Http\Services\WebSite\GetPrivacyPolicyPage\Controller;

use App\Http\Services\WebSite\GetPrivacyPolicyPage\Logic\GetPrivacyPolicyPageInput;
use App\Http\Services\WebSite\GetPrivacyPolicyPage\Logic\GetPrivacyPolicyPageLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\WebSite\GetPrivacyPolicyPage\Request\GetPrivacyPolicyPageRequest;

class GetPrivacyPolicyPageController extends Controller
{
    public function __invoke(GetPrivacyPolicyPageRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetPrivacyPolicyPageInput($request->validated());

        $service = new GetPrivacyPolicyPageLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        //return SendResponse::sendSuccessResponse($result); // send response..
    }
}