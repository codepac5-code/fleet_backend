<?php
namespace App\Http\Repositories\OfficeSubServicePriceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\OfficeSubServicePrice;

class OfficeSubServicePriceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new OfficeSubServicePrice();
    }

}