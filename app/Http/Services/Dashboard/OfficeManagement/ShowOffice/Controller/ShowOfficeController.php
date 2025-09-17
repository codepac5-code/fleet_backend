<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Controller;

use Carbon\Carbon;
use App\Models\Office;
use App\Models\Booking;
use App\Models\OfficePayout;
use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Logic\ShowOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Logic\ShowOfficeLogic;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Request\ShowOfficeRequest;
use App\Models\WalletTransaction;

class ShowOfficeController extends Controller
{
    public function __invoke(ShowOfficeRequest $request)
    {


        $id =  $request->officeId;

        $office = Office::find($id);
        $pendingAmount = FleetWalletRedisModel::getBalanceValueByStatus(BalanceStatus::$Pending) ?? 0;
        // $driverDues = Driver::sum('officeDues');
        // $officeDues = $office->fleetDues;


        $walletBalance = $office->walletBalance;
  




        $orders = Booking::query()->where('officeId', $id);
    

        
        $orders = $orders->where('status', OrderStatus::$Completed);
                        //  ->whereBetween('created_at', [$request->start, $request->end]);
        
        $paymentStats = (clone $orders)
            ->selectRaw("
                COUNT(CASE WHEN paymentType = 'electronic' THEN 1 END) AS electronicPayments,
                COUNT(CASE WHEN paymentType = 'cash' THEN 1 END) AS cashPayments,
                COUNT(CASE WHEN paymentType = 'fleet wallet' THEN 1 END) AS walletPayments,
                COUNT(*) as trips
            ")
            ->first();
        
        $totalRevenue = WalletTransaction::where('to_type', get_class($office))
            ->where('to_id', $id)
            // ->whereBetween('created_at', [$request->start, $request->end])
            ->sum('amount');
        
        $walletWithdrawn = WalletTransaction::where('from_type', get_class($office))
            ->where('from_id', $id)
            // ->whereBetween('created_at', [$request->start, $request->end])
            ->sum('amount');
        
    






        $id =  $request->officeId;
        $auth_user = authSession();
        $office = Office::with('officeDocument', 'booking')->where('id', $id)->first();

        $startDate = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : now()->startOfDay();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->endOfDay() : now()->endOfDay();

        $data = Booking::where('officeId', $id)
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'COUNT(CASE WHEN status = "Pending" THEN "pending" END) AS pendingStatusCount,
                 COUNT(CASE WHEN status = "Cancelled"  THEN "Cancelled" END) AS cancelledstatuscount,
                 COUNT(CASE WHEN status = "Completed"  THEN "Completed" END) AS Completedstatuscount,
                 COUNT(CASE WHEN status = "Accepted"  THEN "Accepted" END) AS Acceptedstatuscount,
                 COUNT(CASE WHEN status = "Ongoing"  THEN "Ongoing" END) AS Ongoingstatuscount'
            )
            ->first()
            ->toArray();

            $officeTotEarning = $totalRevenue;

        // $totalAmount = $officeTotEarning->officeBooking->sum('totalAmount');

        //$totalAmount = $officeTotEarning->officeBooking->whereBetween('created_at', [$startDate, $endDate])->sum('totalAmount');


        $officePayout = WalletTransaction::where('from_type', get_class($office))
            ->where('from_id', $id)
            ->where('to_id', null)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');


       
        $officeData = [
            'officeTotEarning'          =>  $totalRevenue,
            'officeTotWithdrableAmt'    =>  $walletBalance,
            'officeAlreadyWithdrawAmt'  => $walletWithdrawn,
            'pendWithdrwan' =>  $totalRevenue - $officePayout,
        ];

        $fleetDues = $office->fleetDues;


        $pageTitle = __('messages.view_form_title', ['form' => __('messages.office')]);

        return view('office.view', compact('pageTitle', 'fleetDues','office', 'auth_user', 'data', 'officeTotEarning', 'officePayout', 'officeData'));


        // validate input data and pass it to the service..
        $input = new ShowOfficeInput($request->validated());

        $service = new ShowOfficeLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
