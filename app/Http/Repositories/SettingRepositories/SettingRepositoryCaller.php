<?php
namespace App\Http\Repositories\SettingRepositories;
use App\Models\{Setting};

class SettingRepositoryCaller{

    static public function createRepository(){return (new SettingCreateRepository());}
    static public function readRepository(){return (new SettingReadRepository());}
    static public function updateRepository(){return (new SettingUpdateRepository());}
    static public function deleteRepository(){return (new SettingDeleteRepository());}
    static public function get_model() : Setting {return (new Setting());}


}