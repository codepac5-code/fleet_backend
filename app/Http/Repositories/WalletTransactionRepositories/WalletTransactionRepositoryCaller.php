<?php
namespace App\Http\Repositories\WalletTransactionRepositories;
use App\Models\{WalletTransaction};

class WalletTransactionRepositoryCaller{

    static public function createRepository(){return (new WalletTransactionCreateRepository());}
    static public function readRepository(){return (new WalletTransactionReadRepository());}
    static public function updateRepository(){return (new WalletTransactionUpdateRepository());}
    static public function deleteRepository(){return (new WalletTransactionDeleteRepository());}
    static public function get_model() : WalletTransaction {return (new WalletTransaction());}


}