<?php
namespace App\Http\Repositories\WalletTransactionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\WalletTransaction;

class WalletTransactionDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new WalletTransaction();
    }
}