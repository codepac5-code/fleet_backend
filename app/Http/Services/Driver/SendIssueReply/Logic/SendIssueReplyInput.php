<?php
namespace App\Http\Services\Driver\SendIssueReply\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SendIssueReplyInput implements InputServiceInterface
{


    private $content;
    private $image;
    private $issueId;
    public function __construct( array $input)
    {
        $this->content = $input['content'];
        $this->issueId = $input['issueId'];
        $this->image   = $input['image'] ?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getContent(){
        return $this->content;
    }

    public function getImage(){
        return $this->image;
    }
    public function getIssueId(){
        return $this->issueId;
    }
}