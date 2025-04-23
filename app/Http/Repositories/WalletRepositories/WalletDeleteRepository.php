<?php
namespace App\Http\Repositories\WalletRepositories;
use App\Repositories\basic\DeleteRepository;
use App\Models\Wallet;

class WalletDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Wallet();
    }
}