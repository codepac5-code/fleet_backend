<?php
namespace App\Http\Repositories\SettingsRepositories;
use App\Models\{Settings};

class SettingsRepositoryCaller{

    static public function createRepository(){return (new SettingsCreateRepository());}
    static public function readRepository(){return (new SettingsReadRepository());}
    static public function updateRepository(){return (new SettingsUpdateRepository());}
    static public function deleteRepository(){return (new SettingsDeleteRepository());}
    static public function get_model() : Settings {return (new Settings());}


}