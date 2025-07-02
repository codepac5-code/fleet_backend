<?php
namespace App\Http\Repositories\OfficeServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\OfficeService;

class OfficeServiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new OfficeService();
    }

}