<?php
namespace App\Http\Services\Driver\GetDriverWalletHistory\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetDriverWalletHistoryLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetDriverWalletHistoryInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $driver = getAuthUser();

        $transactions = $this->repository->WalletTransactionRepository()->readRepository()
        ->getUserWalletTransactions_paginate($driver , 8);
        
        $data  ['walletBalance'] = $driver->walletBalance;
        $data  ['transactions']  = $transactions ;
        $data  ['officeDues']    = $driver->officeDues + $driver->fleetDues;
        
        $response  = new GetDriverWalletHistoryOutput($data , 'get driver wallet history');
        return $response->send_as_object();
   }
}