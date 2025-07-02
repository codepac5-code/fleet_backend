<?php
namespace App\Http\Services\User\DeleteUserAccount\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DeleteUserAccountLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DeleteUserAccountInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $user = getAuthUser();
        $this->repository->UserRepository()->deleteRepository()->forceDelete(['id'=>$user->id]);
        // $user->currentAccessToken()->delete();
        $response  = new DeleteUserAccountOutput([] , 'account deleted successfully');
        return $response->send_as_array();
   }
}