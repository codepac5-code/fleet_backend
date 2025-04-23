<?php
namespace App\Http\Repositories\DrieverWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DrieverWallet;

class DrieverWalletCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DrieverWallet();
    }
}