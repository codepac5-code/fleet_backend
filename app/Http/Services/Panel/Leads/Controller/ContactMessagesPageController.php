<?php

namespace App\Http\Services\Panel\Leads\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ContactMessagesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $query = ContactMessage::query()->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return view('panel.leads.contact-messages', [
            'entity' => $scope->guard(),
            'messages' => $query->limit(200)->get(),
            'statusFilter' => $status,
            'counts' => [
                'new' => ContactMessage::query()->where('status', 'new')->count(),
                'read' => ContactMessage::query()->where('status', 'read')->count(),
                'total' => ContactMessage::query()->count(),
            ],
        ]);
    }
}
