<?php
namespace App\Http\Repositories\WalletRepositories;

class WalletRepositoryCaller{

    static public function createRepository(){return (new WalletCreateRepository());}
    static public function readRepository(){return (new WalletReadRepository());}
    static public function updateRepository(){return (new WalletUpdateRepository());}
    static public function deleteRepository(){return (new WalletDeleteRepository());}
    static public function get_model() : Wallet {return (new Wallet());}


}