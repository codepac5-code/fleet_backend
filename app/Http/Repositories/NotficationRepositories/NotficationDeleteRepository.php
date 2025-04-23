<?php
namespace App\Http\Repositories\NotficationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Notfication;

class NotficationDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Notfication();
    }
}