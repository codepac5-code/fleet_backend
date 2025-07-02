<?php
namespace App\Http\Repositories\SettingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Setting;

class SettingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Setting();
    }

}