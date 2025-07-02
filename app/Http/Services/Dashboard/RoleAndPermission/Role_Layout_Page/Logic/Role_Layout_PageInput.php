<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class Role_Layout_PageInput implements InputServiceInterface
{
    private $tabpage;
    private $status;

    public function __construct( array $input)
    {
        $this->tabpage = $input['tabpage'];
        $this->status = $input['status'] ?? null;

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of tabpage
     */
    public function getTabpage() {
        return $this->tabpage;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }
}