<?php
namespace App\Http\Repositories\UserWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\UserWallet;

class UserWalletDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new UserWallet();
    }
}