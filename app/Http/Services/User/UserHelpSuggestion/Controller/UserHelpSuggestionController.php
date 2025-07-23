<?php
namespace App\Http\Services\User\UserHelpSuggestion\Controller;

use App\Http\Services\User\UserHelpSuggestion\Logic\UserHelpSuggestionInput;
use App\Http\Services\User\UserHelpSuggestion\Logic\UserHelpSuggestionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\UserHelpSuggestion\Request\UserHelpSuggestionRequest;

class UserHelpSuggestionController extends Controller
{
    public function __invoke(UserHelpSuggestionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UserHelpSuggestionInput($request->validated());

        $service = new UserHelpSuggestionLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}