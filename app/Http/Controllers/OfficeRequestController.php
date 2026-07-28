<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficeRequest;
use App\Http\Core\Classes\Notification\LeadMailer;

class OfficeRequestController extends Controller
{

    public function index()
    {
        $new = OfficeRequest::where('status', 'new')->latest()->get();
        $reviewed = OfficeRequest::where('status', 'reviewed')->latest()->get();


        return view('office.office_requests', compact('new', 'reviewed'));
    }

    public function updateStatus($id)
    {
        $request = OfficeRequest::findOrFail($id);
        $request->status = 'reviewed';
        $request->save();

        return response()->json([
            'message' => 'تم تحديث الحالة'
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'office_name' => 'required',
            'contact_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'city' => 'required',
            'country' => 'required',
            'website' => 'nullable|url',

            'business_category' => 'required',
            'fleet_size' => 'required|integer|min:1',
            'service_type' => 'required',
            'current_tools' => 'nullable',
            'coverage' => 'nullable',

            'license_status' => 'required',
            'timeline' => 'required',
            'notes' => 'nullable',
        ]);

        OfficeRequest::create($data);

        LeadMailer::notify('New office application: ' . $data['office_name'], [
            'Office' => $data['office_name'],
            'Contact' => $data['contact_name'],
            'Email' => $data['email'],
            'Phone' => $data['phone'],
            'City' => $data['city'],
            'Country' => $data['country'],
            'Fleet size' => $data['fleet_size'],
            'Service type' => $data['service_type'],
            'Timeline' => $data['timeline'],
        ]);

        return response()->json([
            'message' => 'تم الإرسال بنجاح'
        ]);
    }
}
