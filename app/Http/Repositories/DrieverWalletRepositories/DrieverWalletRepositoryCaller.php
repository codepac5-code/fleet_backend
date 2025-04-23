<?php
namespace App\Http\Repositories\DrieverWalletRepositories;
use App\Models\{DrieverWallet};

class DrieverWalletRepositoryCaller{

    static public function createRepository(){return (new DrieverWalletCreateRepository());}
    static public function readRepository(){return (new DrieverWalletReadRepository());}
    static public function updateRepository(){return (new DrieverWalletUpdateRepository());}
    static public function deleteRepository(){return (new DrieverWalletDeleteRepository());}
    static public function get_model() : DrieverWallet {return (new DrieverWallet());}


}