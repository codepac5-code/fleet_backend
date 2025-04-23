<?php
namespace App\Http\Repositories\OfficeWalletRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\OfficeWallet;

class OfficeWalletReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new OfficeWallet();
    }

}