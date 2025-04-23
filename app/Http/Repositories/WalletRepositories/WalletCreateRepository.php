<?php
namespace App\Http\Repositories\WalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Wallet;

class WalletCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Wallet();
    }
}