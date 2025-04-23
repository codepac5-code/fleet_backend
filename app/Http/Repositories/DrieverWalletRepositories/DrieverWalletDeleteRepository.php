<?php
namespace App\Http\Repositories\DrieverWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DrieverWallet;

class DrieverWalletDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DrieverWallet();
    }
}