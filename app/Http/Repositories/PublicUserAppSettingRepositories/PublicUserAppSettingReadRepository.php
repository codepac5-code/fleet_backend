<?php
namespace App\Http\Repositories\PublicUserAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\PublicUserAppSetting;

class PublicUserAppSettingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new PublicUserAppSetting();
    }

}