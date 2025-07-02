<?php
namespace App\Http\Repositories\SiteSettingsRepositories;
use App\Models\{SiteSettings};

class SiteSettingsRepositoryCaller{

    static public function createRepository(){return (new SiteSettingsCreateRepository());}
    static public function readRepository(){return (new SiteSettingsReadRepository());}
    static public function updateRepository(){return (new SiteSettingsUpdateRepository());}
    static public function deleteRepository(){return (new SiteSettingsDeleteRepository());}
    static public function get_model() : SiteSettings {return (new SiteSettings());}


}