<?php
namespace App\Http\Repositories\PublicDriverAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\PublicDriverAppSetting;

class PublicDriverAppSettingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new PublicDriverAppSetting();
    }
}