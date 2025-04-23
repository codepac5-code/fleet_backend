<?php
namespace App\Http\Services\Dashboard\RatingManagement\ShowRating\Controller;

use App\Http\Services\Dashboard\RatingManagement\ShowRating\Logic\ShowRatingInput;
use App\Http\Services\Dashboard\RatingManagement\ShowRating\Logic\ShowRatingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RatingManagement\ShowRating\Request\ShowRatingRequest;

class ShowRatingController extends Controller
{
    public function __invoke(ShowRatingRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ShowRatingInput($request->validated());

        $service = new ShowRatingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}