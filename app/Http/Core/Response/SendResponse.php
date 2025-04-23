<?php
namespace App\Http\Core\Response;

use Illuminate\Support\Facades\View;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Core\Response\Adapter\Presenters\ViewPresenter;
use App\Http\Core\Response\Adapter\Presenters\JsonHttpPresenter;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class SendResponse  
{
    function __construct() {}



    //--------- SEND SUCCESS RESPONSE (JSON OR VIEW)-------------

    public static function sendSuccessResponse(ResponseModel $data ) {
        if (request()->expectsJson() || request()->hasHeader('api/*')) {
            return (new JsonHttpPresenter())->sendSuccessJson($data);
        }
        return (new ViewPresenter())->sendSuccessView($data);
    }


    
    //--------- SEND FILED RESPONSE (JSON OR VIEW)-------------
    public static function sendFiledResponse(ResponseModel  $data) {
        if (request()->expectsJson() || request()->hasHeader('api/*')) {
            return (new JsonHttpPresenter())->sendFiledJson($data);
        }
       return (new ViewPresenter())->sendFiledView($data);

    }


    // ----------- SEND EXCEPIION RESPONSE ----

    public static function sendExceptionResponse(ResponseModel  $data) {
        if (request()->expectsJson()) {
            return (new JsonHttpPresenter())->sendFiledJson($data);
        }
    //    return $data->getExceptionAsArray();
    }


    
    /// ------------ JSON RESPONSE FUNCTIONS -----------------------///

    public static function send_json_response($response , $status_code = 200) {
            return (new JsonHttpPresenter())->comman_custom_response( $response, $status_code  );
    }



    // -------------- View RESPONSE FUNCTIONS -------------// 
    public static function send_view_response(){
        return (new ViewPresenter());
    }

}
