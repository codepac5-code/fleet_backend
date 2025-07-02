<?php
namespace App\Http\Services\Driver\DeleteDriverAccount\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DeleteDriverAccountLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DeleteDriverAccountInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        $driver = getAuthUser();
        $this->repository->DriverRepository()->deleteRepository()->forceDelete(['id'=> $driver->id]);
        // $driver->currentAccessToken()->delete();
        $response  = new DeleteDriverAccountOutput([] , 'account deleted successfully');
        return $response->send_as_array();
   }
}