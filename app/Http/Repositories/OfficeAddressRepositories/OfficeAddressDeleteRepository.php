<?php
namespace App\Http\Repositories\OfficeAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\OfficeAddress;

class OfficeAddressDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new OfficeAddress();
    }
}