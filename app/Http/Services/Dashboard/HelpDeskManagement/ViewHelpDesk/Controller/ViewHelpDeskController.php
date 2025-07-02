<?php
namespace App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Controller;

use App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Logic\ViewHelpDeskInput;
use App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Logic\ViewHelpDeskLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Request\ViewHelpDeskRequest;

class ViewHelpDeskController extends Controller
{
    public function __invoke(ViewHelpDeskRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewHelpDeskInput($request->all());

        $service = new ViewHelpDeskLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}