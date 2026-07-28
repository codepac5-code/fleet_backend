<?php
namespace App\Http\Repositories\OfficeSubServicePriceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\OfficeSubServicePrice;

class OfficeSubServicePriceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new OfficeSubServicePrice();
    }
}