<?php
namespace App\Http\Services\Dashboard\DriverJobApplicationsMangement\ToView;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;

class IndexDriverJobApplicationController extends Controller
{
    public function __invoke(Request $request)
    {
        $offices = Office::all();
        return view('driver-applications.index', compact('offices'));
    }
}
