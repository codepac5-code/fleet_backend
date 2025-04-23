<?php
namespace App\Http\Repositories\FrontendSettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\FrontendSetting;

class FrontendSettingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new FrontendSetting();
    }

}