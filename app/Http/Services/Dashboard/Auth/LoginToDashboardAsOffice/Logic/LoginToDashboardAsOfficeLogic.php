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

    private RepositoryCaller $repository; // access to all model's repositories
    private LoginToDashboardAsOfficeInput $input; // added property to hold input

    public function __construct(
        LoginToDashboardAsOfficeInput $input /*| Pass Request To Service*/
    ){
        $this->input = $input;
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute(): ResponseModel | JsonResponse | View | RedirectResponse
    {
        $credentials = [
            'email'    => $this->input->getEmail(),
            'password' => $this->input->getPassword(),
        ];

        logoutAuthUser();

        $guard = $this->input->getGuardName();

        if (!$guard) {
            return redirect()->back()
                ->withErrors(['role' => 'نوع الحساب غير صالح أو غير معرف']);
        }

        if (!authenticate($credentials, $this->input->getRemember(), $guard)) {
            return redirect()->back()
                ->withErrors(['password' => 'كلمة المرور غير صحيحة']);
        }

        session()->regenerate();

        $message = 'welcome to fleet';

        if(checkGuard(Guard::$Employee)){
            
            // $roles = MainRoles();
            // if(!authUserHashRoles(['roles']))
            $message = 'مرحباً بك في فلييت';
            return redirect(route('booking.index'))->withSuccess($message);
        }

        return redirect(route('home'))->withSuccess($message);


    }
}
