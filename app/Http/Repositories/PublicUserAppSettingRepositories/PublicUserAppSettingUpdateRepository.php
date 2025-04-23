<?php
namespace App\Http\Repositories\PublicUserAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\PublicUserAppSetting;

class PublicUserAppSettingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new PublicUserAppSetting();
    }

}