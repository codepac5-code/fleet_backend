<?php
namespace App\Http\Repositories\PublicUserAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\PublicUserAppSetting;

class PublicUserAppSettingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new PublicUserAppSetting();
    }
}