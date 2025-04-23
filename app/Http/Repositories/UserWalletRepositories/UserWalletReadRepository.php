<?php
namespace App\Http\Repositories\UserWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\UserWallet;

class UserWalletReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new UserWallet();
    }

}