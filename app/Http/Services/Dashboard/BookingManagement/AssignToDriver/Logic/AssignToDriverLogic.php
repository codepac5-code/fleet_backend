<?php
namespace App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Logic;
use App\Events\HoldOrder;
use App\Events\NewOrder;
use App\Http\Core\Const\Selected\SelectByLanguage;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Jobs\SendNewOrderForDriversJob;

class AssignToDriverLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AssignToDriverInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $order = $this->repository->BookingRepository()->readRepository()->find($this->input->getOrderId());
        $driver = $this->repository->DriverRepository()->readRepository()->find($this->input->getDriverId());

        if (!$order || !$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Order or Driver not found'
            ], 404);
        }

        if($order->driverId != null){
            event(new HoldOrder($order->id));
        }

        $order->driverId = $driver->id;
        // $order->status = 'OnGoing';
        $order->save();

        $sub_service = $this->repository->SubServiceRepository()->readRepository()
         ->getFirstByConditions(['id'=>$order->subServiceId],SelectByLanguage::subService());

        $payment_methods = $this->repository->PaymentMethodRepository()->readRepository()
        ->getFirstByConditions(['id'=>$order->paymentId] , SelectByLanguage::paymentMethod());

        $waypoints = [];

        if (!empty($order->multiDestnationArray)) {
            $decoded = json_decode($order->multiDestnationArray, true);
            $waypoints = is_array($decoded) ? $decoded : [];
        }

        $data = [
            'startAddress'   => (string) $order->startAddress,
            'endAddress'     => (string) $order->endAddress,
            'time'           => (string) $order->time,

            'startLatitude'  => $order->startLatitude !== null ? (float) $order->startLatitude : null,
            'startLongitude' => $order->startLongitude !== null ? (float) $order->startLongitude : null,
            'endLatitude'    => $order->endLatitude !== null ? (float) $order->endLatitude : null,
            'endLongitude'   => $order->endLongitude !== null ? (float) $order->endLongitude : null,

            'distance'       => $order->distance !== null ? (float) $order->distance : 0.0,

            'couponCode'     => $order->couponCode,
            'subService'     => $sub_service->name,
            'subServiceId'   => (int) $sub_service->id,
            'userId'         => (int) $order->userId,
            'orderId'        => (int) $order->id,

            'paymentMethod'  => $payment_methods->name,

            'totalAmount'    => (float) $order->totalAmount,
            'amount'         => (float) $order->amount,

            'waypoints'      => $waypoints,

            'is_scheduled'   => (bool) $order->is_scheduled,
            'scheduled_time' => $order->scheduled_time,
        ];


//         $data = [
//     'startAddress'   => 'المزة – دمشق',
//     'endAddress'     => 'باب توما – دمشق',
//     'time'           => 25,
//     'startLatitude'  => 33.514805,
//     'startLongitude' => 36.276528,
//     'endLatitude'    => 33.513893,
//     'endLongitude'   => 36.308456,
//     'distance'       => 8.4,
//     'couponCode'     => 'DISCOUNT10',
//     'subService'     => 'تاكسي عادي',
//     'subServiceId'   => 3,
//     'userId'         => 125,
//     'orderId'        => 9876,
//     'paymentMethod'  => 'Cash',
//     'totalAmount'    => 15000,
//     'amount'         => 15000,
//     'waypoints'      => [],
//     'is_scheduled'   => false,
//     'scheduled_time' => null
// ];


        // event(new NewOrder($data,  $this->input->getDriverId()));
        // broadcast(new NewOrder($data, driverId: $this->input->getDriverId()));
        SendNewOrderForDriversJob::dispatch([$this->input->getDriverId()] , $data)->onQueue('jobs');

        return response()->json([
            'success' => true,
            'message' => 'تم اسناد السائق بنجاح',
            'order' => $order,
            'driver' => $driver
        ]);

        // $response  = new AssignToDriverOutput([] , '');
        // return $response->send_as_array();
   }
}
