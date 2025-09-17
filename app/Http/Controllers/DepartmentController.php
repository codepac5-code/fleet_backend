<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function create()
{
    $employees = Employee::where([ 'isActive'=>true])->get();

    return view('department.create', compact('employees'));
}

public function store(Request $request)
{
    $request->validate([
        'name_en' => 'required|string|max:255',
        'name_ar' => 'required|string|max:255',
        'employees' => 'nullable|array',
        'employees.*' => 'exists:employees,id',
    ]);

    $department = new Department();
    $department->name_en = $request->name_en;
    $department->name_ar = $request->name_ar;
    $department->save();

    if ($request->has('employees') && is_array($request->employees)) {

        $department->employees()->sync($request->employees);
    } else {
        $department->employees()->detach();
    }

    return redirect()->route('department.index')->with('success', __('messages.department_created_successfully'));
}


public function index()
{
    return view('department.index');
}

public function getData(Request $request)
{
    $query = Department::query();

    return DataTables::of($query)
        ->addColumn('check', function ($department) {
            return '<input type="checkbox" class="form-check-input select-row" value="'.$department->id.'">';
        })
        ->addColumn('employees_count', function ($department) {
            return $department->employees()->count();
        })
        ->addColumn('action', function ($department) {
            return view('department.action', compact('department'))->render();
        })
        ->editColumn('created_at', function ($department) {
            return $department->created_at->format('Y-m-d');
        })
        ->rawColumns(['check', 'action'])
        ->make(true);
}
public function bulkAction(Request $request)
{
    $action = $request->input('action_type');
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json(['message' => 'No departments selected.'], 400);
    }

    switch ($action) {
        case 'delete':
            Department::whereIn('id', $ids)->delete();
            break;

            default:
            return response()->json(['message' => 'Invalid action.'], 400);
    }

    return response()->json(['message' => 'Action completed successfully.']);
}


public function destroy(Department $department)
    {
        try {
            $department->delete();
    
            return response()->json([
                'status' => true,
                'message' => __('messages.deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.error_occurred'),
            ], 500);
        }
    }
    
}


