<?php
namespace App\Http\Repositories\SiteSettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\SiteSettings;

class SiteSettingsReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new SiteSettings();
    }

}