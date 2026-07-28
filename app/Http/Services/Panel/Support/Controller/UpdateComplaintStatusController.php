<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateComplaintStatusController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, int $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_review,resolved,dismissed'],
        ]);

        $record = Complaint::query()->findOrFail($complaint);

        // complaints is global with repeating office/booking ids, so a country or
        // office mismatch is a cross-scope reach → 404, exactly like lost items.
        $country = Complaint::activeCountryCode();
        if ($country !== null && $record->country_code !== null && $record->country_code !== $country) {
            abort(404);
        }

        if (! $scope->isAdmin() && (int) $record->office_id !== (int) $scope->officeId()) {
            abort(404);
        }

        $record->status = $data['status'];
        $record->save();

        return back()->with('status', textByLanguage('تم تحديث حالة الشكوى.', 'Complaint status updated.'));
    }
}
