<?php
namespace App\Http\Repositories\OfficeAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\OfficeAddress;

class OfficeAddressCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new OfficeAddress();
    }
}