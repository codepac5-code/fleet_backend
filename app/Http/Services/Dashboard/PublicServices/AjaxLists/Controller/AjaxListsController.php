<?php
namespace App\Http\Services\Dashboard\PublicServices\AjaxLists\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\PublicServices\AjaxLists\Logic\AjaxListsInput;
use App\Http\Services\Dashboard\PublicServices\AjaxLists\Logic\AjaxListsLogic;

class AjaxListsController extends Controller
{

    public function __invoke(Request $request ,$list_type)
    {
        // validate input data and pass it to the service..
        $input = new AjaxListsInput($request->all());

        $service = new AjaxListsLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->{$list_type}();

        if ( !($result instanceof ResponseModel) ){
            return $result;
        }

        return $result; // send response..
    }


    
}