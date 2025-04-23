<?php
namespace App\Http\Repositories\WalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Wallet;

class WalletReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Wallet();
    }

}