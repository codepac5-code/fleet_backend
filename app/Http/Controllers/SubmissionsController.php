<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\DriverJobApplication;
use App\Models\OfficeRequest;
use Illuminate\Http\Request;

class SubmissionsController extends Controller
{
    public function hub()
    {
        $pending = [
            'drivers' => $this->safeCount(fn () => DriverJobApplication::query()->where('status', 'pending')->count()),
            'offices' => $this->safeCount(fn () => OfficeRequest::query()->where('status', 'new')->count()),
            'contacts' => $this->safeCount(fn () => ContactMessage::query()->where('status', 'new')->count()),
        ];
        $totals = [
            'drivers' => $this->safeCount(fn () => DriverJobApplication::query()->count()),
            'offices' => $this->safeCount(fn () => OfficeRequest::query()->count()),
            'contacts' => $this->safeCount(fn () => ContactMessage::query()->count()),
        ];

        return view('panel.admin.submissions-hub', compact('pending', 'totals'));
    }

    public function drivers()
    {
        $pending = DriverJobApplication::query()->where('status', 'pending')->latest()->get();
        $approved = DriverJobApplication::query()->where('status', 'approved')->latest()->get();
        $rejected = DriverJobApplication::query()->where('status', 'rejected')->latest()->get();

        return view('panel.admin.driver-applications', compact('pending', 'approved', 'rejected'));
    }

    public function driverStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $application = DriverJobApplication::query()->findOrFail($id);
        $application->status = $data['status'];
        $application->save();

        return back()->with('status', 'updated');
    }

    private function safeCount(callable $fn): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
