<?php
namespace App\Http\Repositories\PublicDriverAppSettingRepositories;
use App\Models\{PublicDriverAppSetting};

class PublicDriverAppSettingRepositoryCaller{

    static public function createRepository(){return (new PublicDriverAppSettingCreateRepository());}
    static public function readRepository(){return (new PublicDriverAppSettingReadRepository());}
    static public function updateRepository(){return (new PublicDriverAppSettingUpdateRepository());}
    static public function deleteRepository(){return (new PublicDriverAppSettingDeleteRepository());}
    static public function get_model() : PublicDriverAppSetting {return (new PublicDriverAppSetting());}


}