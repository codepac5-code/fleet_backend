<?php
namespace App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Controller;

use App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Logic\CreateOrUpdateUserInput;
use App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Logic\CreateOrUpdateUserLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Request\CreateOrUpdateUserRequest;

class CreateOrUpdateUserController extends Controller
{
    public function __invoke(CreateOrUpdateUserRequest $request)
    {

        // validate input data and pass it to the service..
        $input_data = $request->validated();
        $input_data['has_image'] = $request->hasFile('photo');
        $input = new CreateOrUpdateUserInput($input_data);

        $service = new CreateOrUpdateUserLogic($input); // call the service's logic

        // execute service and get result..
        
        return $service->execute();  // send response..

         
    }
}