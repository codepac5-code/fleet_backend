<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboard\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Repositories\RepositoryCaller;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Validation\ValidationException;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LoginToDashboardLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LoginToDashboardInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // $this->input->authenticate();

        logoutAuthUser();

        $credentials = [
            'email' => $this->input->getEmail(),
            'password' => $this->input->get_password(),
        ];

        if( ! authenticate(  $credentials , $this->input->getRemember() , Guard::$Admin)){
            make_exception( 'You are not allowed to log in from here.');
        }
        

        // session()->regenerate();
        // $user = getAuthUser();

        // if($user->status == 0) {
        //     logoutAuthUser(Guard::$Admin);
        //     return redirect()->back()->withErrors(['message'  =>  __('auth.account_inactive')]);
        // }
        // if($this->input->login == 'user_login' && $user->user_type === 'user'){

        //     $response  = new LoginToDashboardOutput([] , 'redirect to FRONTEND' , '/FRONTEND' ,Redirect::ToRoute);
        //     return $response->send_as_array();
        //     // return redirect(RouteServiceProvider::FRONTEND);
        // }
        
        // elseif($this->input->login == 'user_login' && $user->user_type !== 'user') {
        //     logoutAuthUser($this->input->getGurdName());
        //     make_exception('You are not allowed to log in from here.' );
        //     //return redirect()->back()->withErrors(['message' => 'You are not allowed to log in from here.']);
    
        // }
        // else{

            $response  = new LoginToDashboardOutput([] , 'welcome to fleet dashboard' , 'home' ,Redirect::ToRoute);
            return $response->send_as_array();
            // return redirect(RouteServiceProvider::HOME);
        }
}