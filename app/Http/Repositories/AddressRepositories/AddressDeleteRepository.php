<?php
namespace App\Http\Repositories\AddressRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Address;

class AddressDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Address();
    }
}
