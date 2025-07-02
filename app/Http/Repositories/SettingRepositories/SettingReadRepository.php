<?php
namespace App\Http\Repositories\SettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Setting;

class SettingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Setting();
    }

}