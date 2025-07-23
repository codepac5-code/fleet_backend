<?php
namespace App\Http\Services\Driver\GetIssueDetails\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetIssueDetailsInput implements InputServiceInterface
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