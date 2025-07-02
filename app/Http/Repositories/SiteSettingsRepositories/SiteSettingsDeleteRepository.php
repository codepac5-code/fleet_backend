<?php
namespace App\Http\Repositories\SiteSettingsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\SiteSettings;

class SiteSettingsDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new SiteSettings();
    }
}