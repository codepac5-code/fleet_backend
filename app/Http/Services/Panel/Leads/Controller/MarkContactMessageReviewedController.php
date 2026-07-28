<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class MarkContactMessageReviewedController extends Controller
{
    public function __invoke(int $message): RedirectResponse
    {
        $record = ContactMessage::query()->findOrFail($message);
        $record->status = $record->status === 'read' ? 'new' : 'read';
        $record->save();

        return back()->with('status', textByLanguage('تم تحديث الحالة.', 'Status updated.'));
    }
}
