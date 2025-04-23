<?php
namespace App\Http\Repositories\OfficeAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\OfficeAddress;

class OfficeAddressUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new OfficeAddress();
    }

}