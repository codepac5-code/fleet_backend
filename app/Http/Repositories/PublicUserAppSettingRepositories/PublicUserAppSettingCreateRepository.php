<?php
namespace App\Http\Repositories\PublicUserAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\PublicUserAppSetting;

class PublicUserAppSettingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new PublicUserAppSetting();
    }
}