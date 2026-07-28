<?php
namespace App\Http\Repositories\OfficeSubServicePriceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\OfficeSubServicePrice;

class OfficeSubServicePriceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new OfficeSubServicePrice();
    }
}