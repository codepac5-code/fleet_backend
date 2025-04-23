<?php
namespace App\Http\Repositories\DrieverWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DrieverWallet;

class DrieverWalletUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DrieverWallet();
    }

}