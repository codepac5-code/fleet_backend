<?php
namespace App\Http\Repositories\GeneralSettingRepositories;
use App\Models\{GeneralSetting};

class GeneralSettingRepositoryCaller{

    static public function createRepository(){return (new GeneralSettingCreateRepository());}
    static public function readRepository(){return (new GeneralSettingReadRepository());}
    static public function updateRepository(){return (new GeneralSettingUpdateRepository());}
    static public function deleteRepository(){return (new GeneralSettingDeleteRepository());}
    static public function get_model() : GeneralSetting {return (new GeneralSetting());}


}