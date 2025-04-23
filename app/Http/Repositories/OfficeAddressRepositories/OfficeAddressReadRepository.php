<?php
namespace App\Http\Repositories\OfficeAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\OfficeAddress;

class OfficeAddressReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new OfficeAddress();
    }

}