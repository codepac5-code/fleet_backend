<?php
namespace App\Http\Repositories\SettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Settings;

class SettingsUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Settings();
    }

}