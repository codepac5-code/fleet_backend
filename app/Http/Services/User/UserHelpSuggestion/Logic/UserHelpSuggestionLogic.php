<?php
namespace App\Http\Services\User\UserHelpSuggestion\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UserHelpSuggestionLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserHelpSuggestionInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $help_suggestion = $this->repository->HelpSuggestionRepository()
        ->readRepository()->getAllRecords();
        
        $response  = new UserHelpSuggestionOutput($help_suggestion , 'get user help suggestion');
        return $response->send_as_object();
   }
}