<?php
namespace App\Http\Repositories\NotficationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Notfication;

class NotficationCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Notfication();
    }
}