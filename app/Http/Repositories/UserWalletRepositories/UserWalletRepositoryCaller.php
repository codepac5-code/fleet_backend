<?php
namespace App\Http\Repositories\UserWalletRepositories;
use App\Models\{UserWallet};

class UserWalletRepositoryCaller{

    static public function createRepository(){return (new UserWalletCreateRepository());}
    static public function readRepository(){return (new UserWalletReadRepository());}
    static public function updateRepository(){return (new UserWalletUpdateRepository());}
    static public function deleteRepository(){return (new UserWalletDeleteRepository());}
    static public function get_model() : UserWallet {return (new UserWallet());}


}