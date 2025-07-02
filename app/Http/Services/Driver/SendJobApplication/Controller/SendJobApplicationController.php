<?php
namespace App\Http\Services\Driver\SendJobApplication\Controller;

use App\Http\Services\Driver\SendJobApplication\Logic\SendJobApplicationInput;
use App\Http\Services\Driver\SendJobApplication\Logic\SendJobApplicationLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\SendJobApplication\Request\SendJobApplicationRequest;
use App\Models\DriverJobApplication;
use Illuminate\Support\Facades\Hash;

class SendJobApplicationController extends Controller
{
    public function __invoke(SendJobApplicationRequest $request)
    {


        $data = $request->validated();

        $upload = fn($key, $folder = 'driver_applications') =>
            $request->file($key)->store("$folder/$key", 'public');
    
        $application = DriverJobApplication::create([
            'name' => $data['name'],
            'phoneNumber' => $data['phone'],
            'password' => Hash::make($data['password']),
            // 'officeId' => $data['office'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'year' => $data['year'],
            'color' => $data['color'],
            'plateNumber' => $data['plateNumber'],
    
            // images
            'profileImage' => $upload('profileImage'),
            'idFrontImage' => $upload('idFrontImage'),
            'idBackImage' => $upload('idBackImage'),
            'licenseFrontImage' => $upload('licenseFrontImage'),
            'licenseBackImage' => $upload('licenseBackImage'),
            'mechanicalImage' => $upload('mechanicalImage'),
            'frontCarImage' => $upload('frontCarImage'),
            'backCarImage' => $upload('backCarImage'),
            'rightCarImage' => $upload('rightCarImage'),
            'leftCarImage' => $upload('leftCarImage'),
            'insideCarImage' => $upload('insideCarImage'),
            'frontSeatsImage' => $upload('frontSeatsImage'),
            'backSeatsImage' => $upload('backSeatsImage'),
    
            // 'optionalVideo' => $request->hasFile('optionalVideo')
            //     ? $upload('optionalVideo', 'driver_applications/videos')
            //     : null,
    
            'status' => 'pending',
        ]);
        
    
        return response()->json([
            'statusCode' => 200,
            'message' => 'تم إرسال طلب التوظيف بنجاح ، سيتم مراجعته قريباً',
            'data' =>null,
        ], 200);
     
        
        // validate input data and pass it to the service..
        $input = new SendJobApplicationInput($request->validated());

        $service = new SendJobApplicationLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}