<?php
namespace App\Http\Core\Response\Adapter\Presenters;

use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\Response\Adapter\Presenters\Presenter as PresentersPresenter;

class JsonHttpPresenter  implements PresentersPresenter {
    
    public static function sendSuccessJson(ResponseModel $data) {
        return response()->json([
            'statusCode' => 200,
            'message' => checkLangAndSendMessage($data->getMessage()),
            'data' => $data->getData(),
        ], 200);
    }

    public static function sendFiledJson(ResponseModel $data) {
        return response()->json([
            'statusCode' => $data->getStatus(),
            'message' => checkLangAndSendMessage($data->getMessage()),
            'data' => $data->getData(),
        ], 500);
    }



    function comman_message_response( $message, $status_code = 200){
        return response()->json( [ 'message' => $message ], $status_code );
    }
    
    function comman_custom_response( $response, $status_code = 200 ){
        return response()->json($response,$status_code);
    }
    

}
