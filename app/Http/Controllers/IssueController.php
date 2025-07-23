<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IssueController extends Controller
{
    
    public function index()
    {
        $departments = Department::all();
        $agents = Employee::where('role', 'agent')->get();

        return view('helpdesk.index', compact('departments', 'agents'));
    }

   
    public function data(Request $request)
    {
        $query = Issue::with(['assignedTo', 'department']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('agent')) {
            $query->where('assigned_to_id', $request->agent)
                  ->where('assigned_to_type', \App\Models\Employee::class);
        }

        return DataTables::of($query)
            ->addColumn('check', function ($issue) {
                return '
                    <div class="form-check text-center">
                        <input class="form-check-input select-issue" type="checkbox" name="issues[]" value="'.$issue->id.'">
                    </div>';
            })

            ->editColumn('subject', fn($issue) => e($issue->subject))

            ->editColumn('status', function ($issue) {
                return match ($issue->status) {
                    'open' => '<span class="badge bg-success"><i class="fas fa-door-open me-1"></i> مفتوحة</span>',
                    'processing' => '<span class="badge bg-warning text-dark"><i class="fas fa-spinner me-1"></i> قيد المعالجة</span>',
                    'closed' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> مغلقة</span>',
                    default => $issue->status
                };
            })

            ->editColumn('priority', function ($issue) {
                return match ($issue->priority) {
                    'low' => '<span class="badge bg-info text-dark"><i class="fas fa-arrow-down me-1"></i> منخفضة</span>',
                    'medium' => '<span class="badge bg-primary"><i class="fas fa-equals me-1"></i> متوسطة</span>',
                    'high' => '<span class="badge bg-danger"><i class="fas fa-arrow-up me-1"></i> عالية</span>',
                    default => $issue->priority
                };
            })

            ->addColumn('department', fn($issue) => $issue->department?->name_ar ?? '<span class="text-muted">--</span>')

            ->addColumn('agentName', function ($issue) {
                if ($issue->assignedTo && $issue->assignedTo instanceof \App\Models\Employee) {
                    return '<i class="fas fa-user me-1 text-primary"></i>' . e($issue->assignedTo->firstName . ' ' . $issue->assignedTo->lastName);
                }
                return '<span class="text-muted">--</span>';
            })

            ->editColumn('updated_at', fn($issue) => $issue->updated_at?->format('Y-m-d H:i'))

            ->addColumn('action', function ($issue) {
                $delete = "deleteIssue({$issue->id})";
                $showUrl = route("tickets.show", $issue->id);

                return '
                    <div class="btn-group" role="group">
                        <a href="'.$showUrl.'" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <button onclick="'.$delete.'" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt"></i> حذف
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['check', 'status', 'priority', 'department', 'agentName', 'action'])
            ->make(true);
    }

  
    public function destroy($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();

        return response()->json(['message' => 'تم حذف التذكرة بنجاح']);
    }

    /// -------------------- add


    public function create()
    {
        $employees = Employee::all();
        $departments = Department::all();
        return view('helpdesk.create', compact('employees', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to_id' => 'required|exists:employees,id',
            'assigned_to_type' => 'required|string',  // مثلا 'App\Models\Employee'
            'department_id' => 'nullable|exists:departments,id',
            'mode' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,processing,closed',
        ]);

        $issue = new Issue();
        $issue->owner_id = auth()->id();        
        $issue->owner_type = 'App\Models\User'; 
        $issue->subject = $request->subject;
        $issue->description = $request->description;
        $issue->assigned_to_id = $request->assigned_to_id;
        $issue->assigned_to_type = $request->assigned_to_type;
        $issue->department_id = $request->department_id;
        $issue->mode = $request->mode;
        $issue->priority = $request->priority;
        $issue->status = $request->status;
        $issue->isClosed = $request->status === 'closed' ? true : false;
        $issue->save();

        return redirect()->route('issues.index')->with('success', 'تم إنشاء البلاغ بنجاح');
    }

    public function edit(Issue $issue)
    {
        $employees = Employee::all();
        $departments = Department::all();
        return view('issues.edit', compact('issue', 'employees', 'departments'));
    }

    public function update(Request $request, Issue $issue)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to_id' => 'required|exists:employees,id',
            'assigned_to_type' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'mode' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,processing,closed',
        ]);

        $issue->subject = $request->subject;
        $issue->description = $request->description;
        $issue->assigned_to_id = $request->assigned_to_id;
        $issue->assigned_to_type = $request->assigned_to_type;
        $issue->department_id = $request->department_id;
        $issue->mode = $request->mode;
        $issue->priority = $request->priority;
        $issue->status = $request->status;
        $issue->isClosed = $request->status === 'closed' ? true : false;
        $issue->save();

        return redirect()->route('issues.index')->with('success', 'تم تحديث البلاغ بنجاح');
    }


    public function getOwnersByType(Request $request)
    {
        $allowedTypes = [
            'user' => \App\Models\User::class,
            'driver' => \App\Models\Driver::class,
            'office' => \App\Models\Office::class,
        ];
    
        $typeKey = $request->query('type');
    
        if (!isset($allowedTypes[$typeKey])) {
            return response()->json([]);
        }
    
        $modelClass = $allowedTypes[$typeKey];
    
        switch ($typeKey) {
            case 'office':
                $owners = $modelClass::select('id', 'officeName as name')->get();
                break;
    
            case 'driver':
                $owners = $modelClass::select('id', DB::raw("CONCAT_WS(' ', firstName, lastName) as name"))->get();
                break;
            case 'user':
                $owners = $modelClass::select('id', DB::raw("CONCAT_WS(' ', firstName, lastName) as name"))->get();
                break;
    
            default:
                $owners = collect();
                break;
        }
    
        return response()->json($owners);
    }
    
}
