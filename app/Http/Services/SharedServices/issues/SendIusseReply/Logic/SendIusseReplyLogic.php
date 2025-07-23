<?php
namespace App\Http\Services\SharedServices\issues\SendIusseReply\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class SendIusseReplyLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SendIusseReplyInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $ImageManager = new ImageManager();
        
        if($this->input->getImage() != null){
             $path = $ImageManager->upload($this->input->getImage(), $path = 'issues/replies');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = null;
        }

        $issue = $this->repository->ReplyRepository()
        ->createRepository()->createNewReplyByAuthUser(
          issueId: $this->input->getIssueId(),
          content: $this->input->getContent(),
          imageUrl: $path,
        );


        if(!$issue){
            make_exception(__('messages.something_wrong'));
        }


        $response  = new SendIusseReplyOutput([] , 'reply sending..');
        return $response->send_as_object();
   }
}