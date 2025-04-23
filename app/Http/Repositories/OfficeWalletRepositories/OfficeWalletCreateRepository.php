<?php
namespace App\Http\Repositories\OfficeWalletRepositories;

use App\Models\OfficeWallet;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;

class OfficeWalletCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new OfficeWallet();
    }
}