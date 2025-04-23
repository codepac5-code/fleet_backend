<?php
namespace App\Http\Repositories\UserWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\UserWallet;

class UserWalletCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new UserWallet();
    }
}