<?php
namespace App\Http\Repositories\ReplyRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Reply;

class ReplyUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Reply();
    }

}