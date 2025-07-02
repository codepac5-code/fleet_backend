<?php
namespace App\Http\Repositories\OfficeServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\OfficeService;

class OfficeServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new OfficeService();
    }
}