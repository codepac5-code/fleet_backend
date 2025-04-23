<?php
namespace App\Http\Repositories\SettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Settings;

class SettingsReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Settings();
    }

}