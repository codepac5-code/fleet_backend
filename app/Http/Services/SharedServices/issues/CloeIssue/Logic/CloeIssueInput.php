<?php
namespace App\Http\Services\SharedServices\issues\CloeIssue\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CloeIssueInput implements InputServiceInterface
{
    private $issueId;
    public function __construct( array $input)
    {
        $this->issueId = $input['issueId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of issueId
     */
    public function getIssueId() {
        return $this->issueId;
    }
}