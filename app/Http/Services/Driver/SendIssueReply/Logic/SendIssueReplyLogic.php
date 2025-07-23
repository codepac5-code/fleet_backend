<?php
namespace App\Http\Services\Driver\SendIssueReply\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class SendIssueReplyLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SendIssueReplyInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $ImageManager = new ImageManager();
        
        if($this->input->getImage() != null){
             $path = $ImageManager->upload($this->input->getImage(), $path = 'user/issue');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = null;
        }

        $user = getAuthUser();
        $issue = $this->repository->ReplyRepository()
        ->createRepository()->create([
            'issueId'=>$this->input->getIssueId(),
            'sender_id' =>$user->id,
            'sender_type' => get_class($user),
            'senderName'=>$user->firstName .' '.$user->lastName,
            'imageUrl'=> $path,
            'content'=>$this->input->getContent(),
        ]);


        if(!$issue){
            make_exception(__('messages.something_wrong'));
        }



        $response  = new SendIssueReplyOutput([] , 'reply sending..');
        return $response->send_as_object();
   }
}