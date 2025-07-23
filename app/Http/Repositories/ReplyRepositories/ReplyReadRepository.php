<?php
namespace App\Http\Repositories\ReplyRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Reply;

class ReplyReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Reply();
    }

}