<?php
namespace App\Http\Repositories\CommissionsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Commissions;

class CommissionsDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Commissions();
    }
}