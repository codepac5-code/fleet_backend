<?php
namespace App\Http\Repositories\ReplyRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Reply;

class ReplyCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Reply();
    }


    public function createNewReplyByAuthUser( $issueId ,$content, $imageUrl = null){
        $user = auth()->user();
        return $user->replies()->create([
            'issueId' => $issueId,
            'senderName'=>$user->firstName .' '.$user->lastName,
            'content' => $content,
            'imageUrl' => $imageUrl
        ]);
    }
}