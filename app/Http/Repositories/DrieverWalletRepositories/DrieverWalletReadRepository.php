<?php
namespace App\Http\Repositories\DrieverWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\DrieverWallet;

class DrieverWalletReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new DrieverWallet();
    }

}