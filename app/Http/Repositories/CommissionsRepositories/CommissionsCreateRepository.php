<?php
namespace App\Http\Repositories\CommissionsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Commissions;

class CommissionsCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Commissions();
    }
}