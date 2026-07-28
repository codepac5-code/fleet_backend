<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Request\StoreEmployeeRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class StoreEmployeeController extends Controller
{
    public function __invoke(StoreEmployeeRequest $request, EntityScope $scope, EmployeeRepository $employees): RedirectResponse
    {
        $data = $request->payload();

        if (! $scope->isAdmin()) {
            $data['officeId'] = $scope->officeId();
        }

        $employees->create($data);

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', textByLanguage('تمت إضافة الموظف بنجاح', 'Employee created successfully'));
    }
}
