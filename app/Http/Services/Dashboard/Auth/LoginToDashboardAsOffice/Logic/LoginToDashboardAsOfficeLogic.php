<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LoginToDashboardAsOfficeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LoginToDashboardAsOfficeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $credentials = [
            'email' => $this->input->getEmail(),
            'password' => $this->input->get_password(),
        ];


        if( ! authenticate(  $credentials , $this->input->getRemember() , Guard::$Office)){

            return redirect()->back()
            ->withErrors(['password' => 'incorect password']);
            // make_exception( 'You are not allowed to log in from here.');
        }
        

        session()->regenerate();

        $message = 'welcome to fleet dashboard';
        // return response()->json(['ppp'=>'ppppp']);

        return redirect(route('home'))->withSuccess($message);

        // $response  = new LoginToDashboardAsOfficeOutput([] , 'welcome to fleet dashboard' , 'home' ,Redirect::ToRoute);
        // return $response->send_as_array();
   }
}