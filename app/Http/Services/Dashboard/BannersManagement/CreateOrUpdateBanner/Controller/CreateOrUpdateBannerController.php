<?php
namespace App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Controller;

use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Logic\CreateOrUpdateBannerInput;
use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Logic\CreateOrUpdateBannerLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Request\CreateOrUpdateBannerRequest;

class CreateOrUpdateBannerController extends Controller
{
    public function __invoke(CreateOrUpdateBannerRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateBannerInput($request->validated());

        $service = new CreateOrUpdateBannerLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();// send response..

    }
}