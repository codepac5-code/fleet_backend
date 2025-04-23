<?php
namespace App\Http\Repositories\AddressRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Address;

class AddressUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Address();
    }

}
