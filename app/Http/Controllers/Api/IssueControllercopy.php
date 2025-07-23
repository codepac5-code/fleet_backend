<?php
namespace App\Http\Controllers\Api;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{



    public function index(Request $request)
    {
        $query = Issue::query()->with(['assignedTo', 'department']);

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // فلترة الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة القسم
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // فلترة الموظف
        if ($request->filled('agent')) {
            $query->where('assigned_to', $request->agent);
        }

        // فلترة الأولوية
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }


        $perPage = $request->input('per_page', 10);
        $tickets = $query->latest()->paginate($perPage);

        $formatted = $tickets->getCollection()->transform(function ($ticket) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'department' => optional($ticket->department)->name,
                'agentName' => optional($ticket->assignedTo)->firstName . ' ' . optional($ticket->assignedTo)->lastName,
                'agentImageUrl' => $ticket->assignedTo && $ticket->assignedTo->photo
                    ? asset('storage/' . $ticket->assignedTo->photo)
                    : null,
                'lastUpdated' => $ticket->updated_at,
            ];
        });

        return response()->json([
            'tickets' => $formatted,
            'totalItems' => $tickets->total(),
        ]);
    }

    public function destroy($id)
    {
        $issue = Issue::findOrFail($id);

        // حذف الصورة المرتبطة إن وجدت
        if ($issue->photo && Storage::disk('public')->exists($issue->photo)) {
            Storage::disk('public')->delete($issue->photo);
        }

        $issue->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function filters()
        {
            $departments = Department::select('id', 'name_ar as name')->get();

            $agents = Employee::where('isActive', 1)
            ->select('id', DB::raw("CONCAT(firstName, ' ', lastName) as name"))
            ->get();

            $statuses = Issue::select('status')
                ->distinct()
                ->pluck('status');


            $priorities = Issue::select('priority')->distinct()->pluck('priority');


            return response()->json([
                'departments' => $departments,
                'agents' => $agents,
                'statuses' => $statuses,
            ]);
        }
}
