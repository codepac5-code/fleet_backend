<?php
namespace App\Http\Repositories\OfficeSubServicePriceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\OfficeSubServicePrice;

class OfficeSubServicePriceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new OfficeSubServicePrice();
    }

}