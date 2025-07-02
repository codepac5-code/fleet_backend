<?php
namespace App\Http\Repositories\OfficeServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\OfficeService;

class OfficeServiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new OfficeService();
    }
}