<?php
namespace App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Controller;

use App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Logic\DestroyVBrandInput;
use App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Logic\DestroyVBrandLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Request\DestroyVBrandRequest;

class DestroyVBrandController extends Controller
{
    public function __invoke(DestroyVBrandRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DestroyVBrandInput($request->validated());

        $service = new DestroyVBrandLogic($input); // call the service's logic

        // execute service and get result..
       return $service->execute();// send response..

        // return SendResponse::sendSuccessResponse($result); 
    }
}