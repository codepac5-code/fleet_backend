<?php
namespace App\Http\Repositories\WalletTransactionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\WalletTransaction;

class WalletTransactionUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new WalletTransaction();
    }

}