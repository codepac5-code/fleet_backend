<?php
namespace App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Controller;

use App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Logic\CreateOrUpdateBrandInput;
use App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Logic\CreateOrUpdateBrandLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Request\CreateOrUpdateBrandRequest;

class CreateOrUpdateBrandController extends Controller
{
    public function __invoke(CreateOrUpdateBrandRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateOrUpdateBrandInput($request->all());

        $service = new CreateOrUpdateBrandLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}