<?php
namespace App\Http\Services\Dashboard\GetOfficeReviews\Controller;

use App\Http\Services\Dashboard\GetOfficeReviews\Logic\GetOfficeReviewsInput;
use App\Http\Services\Dashboard\GetOfficeReviews\Logic\GetOfficeReviewsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\GetOfficeReviews\Request\GetOfficeReviewsRequest;

class GetOfficeReviewsController extends Controller
{
    public function __invoke(GetOfficeReviewsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetOfficeReviewsInput($request->validated());

        $service = new GetOfficeReviewsLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}