<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use Illuminate\Http\RedirectResponse;

class ToggleEmployeeStatusController extends Controller
{
    public function __invoke(int $employee, EmployeeRepository $employees): RedirectResponse
    {
        $employees->toggleStatus($employees->findOrFail($employee));

        return back();
    }
}
