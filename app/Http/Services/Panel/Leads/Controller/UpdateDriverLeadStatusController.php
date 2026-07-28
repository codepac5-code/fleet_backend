<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Models\DriverJobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateDriverLeadStatusController extends Controller
{
    public function __invoke(Request $request, int $application): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $record = DriverJobApplication::query()->findOrFail($application);
        $record->status = $data['status'];
        $record->save();

        return back()->with('status', textByLanguage('تم تحديث الحالة.', 'Status updated.'));
    }
}
