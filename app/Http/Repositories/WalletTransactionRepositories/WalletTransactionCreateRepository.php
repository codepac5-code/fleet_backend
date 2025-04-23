<?php
namespace App\Http\Repositories\WalletTransactionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\WalletTransaction;

class WalletTransactionCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new WalletTransaction();
    }
}