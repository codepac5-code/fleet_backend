<?php
namespace App\Http\Services\Dashboard\WalletHistory\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;


class WalletHistoryLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private WalletHistoryInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $pageTitle = 'Wallet History';

        switch($this->input->getUserType()){

            case 'user' : $userdata =$this->repository->UserRepository()
            ->readRepository()->getByValue('id',$this->input->getIdentifier());
            if($userdata == null){
                return redirect()->back()
             ->withErrors(['identifier' => __('messages.invalid_phone')]);
            }
            
            $transactions = $this->repository->WalletTransactionRepository()->readRepository()
            ->getUserWalletTransactions_paginate( $userdata , 8);
            
            $data  ['walletBalance'] = $userdata->walletBalance;
            $data  ['transactions']  = $transactions ;
            $isDriver = false;
            $userType = 'user';
            return view('wallet.user',compact('data','pageTitle','userdata','isDriver','userType'));
            break;
            
            
            case 'driver': 
                $customerdata = $this->repository->DriverRepository()
                ->readRepository()->getByValue('id' , $this->input->getIdentifier());

                if($customerdata == null){
                    return redirect()->back()
                    ->withErrors(['identifier' => __('messages.invalid_phone')]);
                }
                $transactions = $this->repository->WalletTransactionRepository()->readRepository()
                ->getUserWalletTransactions_paginate( $customerdata , 8);
                
                $data  ['walletBalance'] = $customerdata->walletBalance;
                $data  ['officeDues']    = $customerdata->fleetDues + $customerdata->officeDues;
                $data  ['transactions']  = $transactions ;
                $isDriver = true;
                $userType = 'driver';
                $car = $customerdata->vehicle;
                return view('wallet.driver',compact('data','pageTitle','customerdata','isDriver','userType','car'));
                break;


            case 'office':
        }
        return view('wallet.user',compact('data','pageTitle','customerdata'));

        // $response  = new WalletHistoryOutput([] , '');
        // return $response->send_as_array();
   }
}