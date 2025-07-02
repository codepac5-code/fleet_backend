<?php
namespace App\Http\Repositories\OfficeServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\OfficeService;

class OfficeServiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new OfficeService();
    }

}