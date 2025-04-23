<?php
namespace App\Http\Repositories\WalletTransactionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletTransactionReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new WalletTransaction();
    }


    public function getUserWalletTransactions_paginate ( $user , $paginate = 10){

        $select = select_by_language([
            'id',
            'from_type',
            'from_id',
            'to_type',
            'to_id',
            'amount',
            'status',
            'created_at',
            'description',
            'source_type',
            'source_id',
            'paymentName',
            DB::raw("IF(from_type = '" . addslashes(get_class($user)) . "' AND from_id = {$user->id}, true, false) as isWithdraw")
        ] , [
            'id',
            'from_type',
            'from_id',
            'to_type',
            'to_id',
            'amount',
            'status',
            'created_at',
            'description_en as description',
            'source_type',
            'source_id',
            'paymentName_en as paymentName',
            DB::raw("IF(from_type = '" . addslashes(get_class($user)) . "' AND from_id = {$user->id}, true, false) as isWithdraw")
        ]);
        return $this->model->query()->select(
            $select
        )
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('from_type', get_class($user))
                        ->where('from_id', $user->id); 
                })
                ->orWhere(function ($q) use ($user) {
                    $q->where('to_type', get_class($user))
                        ->where('to_id', $user->id); 
                });
            })
            ->orderBy('created_at', 'desc') 
            ->paginate($paginate);
    }

}