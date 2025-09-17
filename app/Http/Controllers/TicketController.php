<?php
namespace App\Http\Controllers;

use App\Http\Core\Models\NotificationModel;
use App\Models\Issue;
use App\Models\Reply;
use App\Models\Employee;
use App\Models\Department;
use App\Notifications\PrivateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
   
    public function show($id)
    {
        $ticket = Issue::with([
            'replies',
            'logs.employee',
            'department',
            'assignedTo',
            'owner',
        ])->findOrFail($id);

        $departments = Department::all();
        $employees = Employee::where('isActive', true)->get();

        return view('helpdesk.tickets.show', compact('ticket', 'employees', 'departments'));
    }

   
    public function update(Request $request, $id)
    {
        $issue = Issue::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|in:open,processing,closed',
            'assigned_to' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'priority' => 'nullable|in:low,medium,high',
            'reply' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $changed = false;

        if ($request->filled('department_id') && $issue->department_id != $request->department_id) {
            $issue->department_id = $request->department_id;
            $changed = true;

            $issue->logs()->create([
                'employee_id' => Auth::id(),
                'employee_type' => get_class(Auth::user()),
                'action' => 'department_changed',
                'note' => 'تم تغيير القسم إلى ' . optional(Department::find($request->department_id))->name_ar,
            ]);
        }

        if ($request->filled('assigned_to') &&
            ($issue->assigned_to_id != $request->assigned_to || $issue->assigned_to_type != Employee::class)) {

            $issue->assigned_to_id = $request->assigned_to;
            $issue->assigned_to_type = Employee::class;
            $changed = true;

            $employeeName = optional(Employee::find($request->assigned_to))->firstName;

            $issue->logs()->create([
                'employee_id' => Auth::id(),
                'employee_type' => get_class(Auth::user()),
                'action' => 'assigned',
                'note' => 'تم تعيين الموظف إلى ' . $employeeName,
            ]);
        }

        if ($request->filled('priority') && $issue->priority != $request->priority) {
            $issue->priority = $request->priority;
            $changed = true;

            $issue->logs()->create([
                'employee_id' => Auth::id(),
                'employee_type' => get_class(Auth::user()),
                'action' => 'priority_changed',
                'note' => 'تم تغيير الأولوية إلى ' . $request->priority,
            ]);
        }

        if ($request->filled('status') && $issue->status != $request->status) {
            $issue->status = $request->status;
            $changed = true;

            $issue->logs()->create([
                'employee_id' => Auth::id(),
                'employee_type' => get_class(Auth::user()),
                'action' => 'status_changed',
                'note' => 'تم تغيير الحالة إلى ' . $request->status,
            ]);
        }

        if ($changed) {
            $issue->save();
        }

        if ($request->filled('reply')) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('replies', 'public');
            }

            Reply::create([
                'issueId' => $issue->id,
                'sender_id' => Auth::id(),
                'sender_type' => get_class(Auth::user()),
                'senderName' => Auth::user()->firstName . ' ' . Auth::user()->lastName,
                'content' => $request->reply,
                'imageUrl' => $imagePath,
            ]);
        }

        return redirect()->route('tickets.show', $issue->id)->with('success', 'تم تحديث التذكرة.');
    }

   
    public function close($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->status = 'closed';
        $issue->isClosed = true;
        $issue->closedAt = now();
        $issue->save();

        $issue->logs()->create([
            'employee_id' => Auth::id(),
            'employee_type' => get_class(Auth::user()),
            'action' => 'closed',
            'note' => 'تم إغلاق التذكرة.',
        ]);

        return redirect()->route('tickets.show', $issue->id)->with('success', 'تم إغلاق التذكرة بنجاح.');
    }

   
    public function replyAjax(Request $request, $id)
    {
        $issue = Issue::findOrFail($id);

        $validated = $request->validate([
            'reply' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('replies', 'public');
        }

        $reply = Reply::create([
            'issueId' => $issue->id,
            'sender_id' => Auth::id(),
            'sender_type' => get_class(Auth::user()),
            'senderName' => Auth::user()->firstName . ' ' . Auth::user()->lastName,
            'content' => $request->reply,
            'imageUrl' => $imagePath,
        ]);


    $notificationModel = new NotificationModel(
        'رد جديد على المشكلة',
        'لقد تلقيت ردًا جديدًا على المشكلة رقم #' . $issue->id,
        'New Reply on Your Issue',
        'You have received a new reply on issue number #' . $issue->id,
        'https://cdn-icons-png.flaticon.com/512/1827/1827373.png', 
        false,
        null //  AppScreenName::HelpDesk_Screen->value 
    );

    if ($issue->owner && method_exists($issue->owner, 'notify')) {
        $issue->owner->notify(new PrivateNotification($notificationModel));
    }

        return response()->json([
            'success' => true,
            'reply' => [
                'content' => nl2br(e($reply->content)),
                'sender_type' => $reply->sender_type == Employee::class ? 'staff' : 'user',
                'imageUrl' => $reply->imageUrl ? asset('storage/' . $reply->imageUrl) : null,
                'timestamp' => $reply->created_at->format('h:i A - Y/m/d'),
                'senderName' => $reply->senderName,
            ],
        ]);
    }

   
    public function fetchReplies($id)
    {
        $ticket = Issue::with('replies')->findOrFail($id);
        $replies = $ticket->replies->map(function ($reply) {
            return [
                'content' => $reply->content,
                'imageUrl' => $reply->imageUrl,
                'created_at' => $reply->created_at->format('h:i A - Y/m/d'),
                'sender_type' => $reply->sender_type,
                'senderName' => $reply->senderName,
            ];
        });
        return response()->json(['replies' => $replies]);
    }
}
