<?php
namespace App\Http\Repositories\AddressRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Address;

class AddressCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Address();
    }
}
