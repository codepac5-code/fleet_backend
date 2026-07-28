<?php

namespace App\Http\Services\Panel\Employees\Logic;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class EmployeeRepository
{
    public function __construct(private EntityScope $scope) {}

    private function query(): Builder
    {
        $query = Employee::on(TenantConnection::current())->newQuery();

        return $this->scope->scopeByOffice($query);
    }

    public function paginate(?string $search, ?int $officeId = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($officeId, fn (Builder $q) => $q->where('officeId', $officeId))
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('firstName', 'like', "%{$search}%")
                        ->orWhere('lastName', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phoneNumber', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Employee
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Employee
    {
        $data['password'] = Hash::make($data['password']);

        $employee = new Employee($data);

        if ($connection = TenantConnection::current()) {
            $employee->setConnection($connection);
        }

        $employee->save();

        return $employee;
    }

    public function update(Employee $employee, array $data): Employee
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $employee->fill($data)->save();

        return $employee;
    }

    public function toggleStatus(Employee $employee): Employee
    {
        $employee->isActive = $employee->isActive ? 0 : 1;
        $employee->save();

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}
