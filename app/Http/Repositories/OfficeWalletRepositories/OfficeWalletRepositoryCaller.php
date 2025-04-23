<?php
namespace App\Http\Repositories\OfficeWalletRepositories;

use App\Models\OfficeWallet;

class OfficeWalletRepositoryCaller{

    static public function createRepository(){return (new OfficeWalletCreateRepository());}
    static public function readRepository(){return (new OfficeWalletReadRepository());}
    static public function updateRepository(){return (new OfficeWalletUpdateRepository());}
    static public function deleteRepository(){return (new OfficeWalletDeleteRepository());}
    static public function get_model() : OfficeWallet {return (new OfficeWallet());}


}