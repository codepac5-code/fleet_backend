<?php
namespace App\Http\Repositories\SiteSettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\SiteSettings;

class SiteSettingsUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new SiteSettings();
    }

}