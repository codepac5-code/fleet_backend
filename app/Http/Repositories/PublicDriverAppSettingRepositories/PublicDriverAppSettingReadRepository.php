<?php
namespace App\Http\Repositories\PublicDriverAppSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\PublicDriverAppSetting;

class PublicDriverAppSettingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new PublicDriverAppSetting();
    }

}