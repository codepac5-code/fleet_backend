<?php
namespace App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Controller;

use App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Logic\ViewBrandListInput;
use App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Logic\ViewBrandListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Request\ViewBrandListRequest;

class ViewBrandListController extends Controller
{
    public function __invoke(ViewBrandListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewBrandListInput($request->validated());

        $service = new ViewBrandListLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}