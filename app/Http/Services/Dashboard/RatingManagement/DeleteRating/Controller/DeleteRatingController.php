<?php
namespace App\Http\Services\Dashboard\RatingManagement\DeleteRating\Controller;

use App\Http\Services\Dashboard\RatingManagement\DeleteRating\Logic\DeleteRatingInput;
use App\Http\Services\Dashboard\RatingManagement\DeleteRating\Logic\DeleteRatingLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RatingManagement\DeleteRating\Request\DeleteRatingRequest;

class DeleteRatingController extends Controller
{
    public function __invoke(DeleteRatingRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteRatingInput($request->validated());

        $service = new DeleteRatingLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}