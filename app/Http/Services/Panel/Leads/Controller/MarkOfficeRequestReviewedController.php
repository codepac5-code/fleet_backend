<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Models\OfficeRequest;
use Illuminate\Http\RedirectResponse;

class MarkOfficeRequestReviewedController extends Controller
{
    public function __invoke(int $request): RedirectResponse
    {
        $record = OfficeRequest::query()->findOrFail($request);
        $record->status = $record->status === 'reviewed' ? 'new' : 'reviewed';
        $record->save();

        return back()->with('status', textByLanguage('تم تحديث الحالة.', 'Status updated.'));
    }
}
