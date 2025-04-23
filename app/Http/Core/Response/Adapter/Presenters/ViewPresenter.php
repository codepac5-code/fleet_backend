<?php
namespace App\Http\Core\Response\Adapter\Presenters;

use Exception;
use Illuminate\Support\Facades\View;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewPresenter extends View implements Presenter
{
    public static function sendSuccessView(ResponseModel $model ) {

         switch ( $model->redirect() ) {
            

            // ---------------------- REDIRECT TO VIEW ------------//
            
            case Redirect::ToView->value :
                if (view()->exists($model->getViewPath())) {
                    $data       = $model->getData();
                    $message    = $model->getMessage();
                    $statusCode = 200;
                 
                return view($model->getViewPath())->compact(  $data , $message , $statusCode);
            }
            throw new Exception('View not found',422);
            break;

            
            
            // ---------------------- REDIRECT TO ROUTE ------------//
            case Redirect::ToRoute->value :
              
            return redirect(route($model->getViewPath()))
            ->withSuccess($model->getMessage())->with($model->getData());
        // throw new Exception('route not found',422);
        break;

                
        
            // ---------------------- REDIRECT BACK ----------------//

          case Redirect::Back->value : 
            return redirect()->back()
            ->withSuccess($model->getMessage())->with($model->getData());
                // throw new Exception('route not found',422);
                break;
        };
       
    }
    

    

    public static function sendFiledView(ResponseModel $model) {

        switch ( $model->redirect() ) {
            
            // ---------------------- REDIRECT TO VIEW ------------//
            
            case Redirect::ToView->value :
                if (view()->exists($model->getViewPath())) {
                return view($model->getViewPath())->with([
                    "data" =>$model->getData(),
                    "message" => $model->getMessage(),
                    'statusCode' => $model->getStatus(),
                ]);
            }
            throw new Exception('View not found',422);
            break;

            
            
            // ---------------------- REDIRECT TO ROUTE ------------//
            case Redirect::ToRoute->value :
                    return redirect(route($model->getViewPath()))
                    ->withErrors($model->getMessage())->with($model->getData());
                // throw new Exception('route not found',422);
                break;
       
                
        
            // ---------------------- REDIRECT BACK ----------------//

           case Redirect::Back->value : 
            return redirect()->back()
            ->withErrors($model->getMessage())->with($model->getData());
                // throw new Exception('route not found',422);
                break;
           };

        // return redirect()->back()->with([
        //     "data" =>$data->getData(),
        //     "message" => $data->getMessage(),
        //     'statusCode' => $data->getStatus(),
        // ]);
    }    

}
