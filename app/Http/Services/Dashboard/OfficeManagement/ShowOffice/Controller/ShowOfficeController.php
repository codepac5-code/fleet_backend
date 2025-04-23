<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Controller;

use Carbon\Carbon;
use App\Models\Office;
use App\Models\Booking;
use App\Models\OfficePayout;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Logic\ShowOfficeInput;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Logic\ShowOfficeLogic;
use App\Http\Services\Dashboard\OfficeManagement\ShowOffice\Request\ShowOfficeRequest;

class ShowOfficeController extends Controller
{
    public function __invoke(ShowOfficeRequest $request)
    {
        $id =  $request->office;
        $auth_user = authSession();
        $office = Office::with('officeDocument', 'booking')->where('id', $id)->first();

        $startDate = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : now()->startOfDay();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->endOfDay() : now()->endOfDay();

        $data = Booking::where('officeId', $id)
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'COUNT(CASE WHEN status = "pending" THEN "pending" END) AS pendingStatusCount,
                 COUNT(CASE WHEN status = "Cancelled"  THEN "Cancelled" END) AS cancelledstatuscount,
                 COUNT(CASE WHEN status = "Completed"  THEN "Completed" END) AS Completedstatuscount,
                 COUNT(CASE WHEN status = "Accepted"  THEN "Accepted" END) AS Acceptedstatuscount,
                 COUNT(CASE WHEN status = "Ongoing"  THEN "Ongoing" END) AS Ongoingstatuscount'
            )
            ->first()
            ->toArray();

            $officeTotEarning = Office::where('id', $id)
            ->first();

        $totalAmount = $officeTotEarning->officeBooking->sum('totalAmount');

        //$totalAmount = $officeTotEarning->officeBooking->whereBetween('created_at', [$startDate, $endDate])->sum('totalAmount');


        $officePayout = OfficePayout::where('officeId', $id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $officeData = [
            'officeTotEarning'          =>  $totalAmount,
            'officeTotWithdrableAmt'    =>  $totalAmount,
            'officeAlreadyWithdrawAmt'  =>  $officePayout,
            'pendWithdrwan' =>  $totalAmount - $officePayout,
        ];

        $pageTitle = __('messages.view_form_title', ['form' => __('messages.office')]);

        return view('office.view', compact('pageTitle', 'office', 'auth_user', 'data', 'officeTotEarning', 'officePayout', 'officeData'));


        // validate input data and pass it to the service..
        $input = new ShowOfficeInput($request->validated());

        $service = new ShowOfficeLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
