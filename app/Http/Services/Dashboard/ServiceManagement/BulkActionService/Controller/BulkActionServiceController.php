<?php
namespace App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Controller;

use App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Logic\BulkActionServiceInput;
use App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Logic\BulkActionServiceLogic;
use App\Http\Controllers\Controller;
use App\Http\Response\SendResponse;
use App\Http\Services\Dashboard\ServiceManagement\BulkActionService\Request\BulkActionServiceRequest;
use App\Models\Service;

class BulkActionServiceController extends Controller
{
    public function __invoke(BulkActionServiceRequest $request)
    {

        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'change-status':

                $branches = Service::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Service Status Updated';
                break;

            case 'change-featured':
                $branches = Service::whereIn('id', $ids)->update(['is_featured' => $request->is_featured]);
                $message = 'Bulk Service Featured Updated';
                break;

            case 'delete':
                Service::whereIn('id', $ids)->delete();
                $message = 'Bulk Service Deleted';
                break;

            case 'restore':
                Service::whereIn('id', $ids)->restore();
                $message = 'Bulk Service Restored';
                break;

            case 'permanently-delete':
                Service::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Service Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false,'is_featured' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'is_featured' => true, 'message' => $message]);

        // // validate input data and pass it to the service..
        // $input = new BulkActionServiceInput($request->validated());

        // $service = new BulkActionServiceLogic($input); // call the service's logic

        // // execute service and get result..
        // $result = $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}
