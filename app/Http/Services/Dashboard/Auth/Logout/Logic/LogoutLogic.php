<?php
namespace App\Http\Services\Dashboard\Auth\Logout\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\View;
use Illuminate\Http\RedirectResponse;


class LogoutLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LogoutInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | RedirectResponse {

        $loginPage = 'login';

        if(auth()->user()->hasAnyRole([Guard::$Admin])){
            $loginPage = 'login-admin';
        }


        logoutAuthUser();

		return redirect(route($loginPage));
   }
}