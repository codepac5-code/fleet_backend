<?php

namespace App\Http\Controllers;

use App\Http\Core\Classes\Notification\LeadMailer;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'intent' => 'required|in:demo,sales,support,waitlist',
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:40',
            'company' => 'nullable|string|max:160',
            'message' => 'nullable|string|max:2000',
        ]);

        ContactMessage::create($data);

        LeadMailer::notify('New contact request (' . $data['intent'] . '): ' . $data['name'], [
            'Reason' => $data['intent'],
            'Name' => $data['name'],
            'Email' => $data['email'],
            'Phone' => $data['phone'] ?? null,
            'Company' => $data['company'] ?? null,
            'Message' => $data['message'] ?? null,
        ]);

        return response()->json([
            'message' => 'received',
        ]);
    }

    public function index()
    {
        $new = ContactMessage::where('status', 'new')->latest()->get();
        $reviewed = ContactMessage::where('status', 'reviewed')->latest()->get();

        return view('office.contact_messages', compact('new', 'reviewed'));
    }

    public function updateStatus($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->status = 'reviewed';
        $message->save();

        return response()->json(['message' => 'updated']);
    }
}
