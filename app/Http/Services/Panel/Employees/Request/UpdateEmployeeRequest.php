<?php

namespace App\Http\Services\Panel\Employees\Request;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $connection = TenantConnection::current();
        $employees = $connection ? $connection . '.employees' : 'employees';
        $offices = $connection ? $connection . '.offices' : 'offices';
        $isAdmin = app(EntityScope::class)->isAdmin();
        $employeeId = (int) $this->route('employee');

        return [
            'firstName'      => ['required', 'string', 'max:30'],
            'lastName'       => ['required', 'string', 'max:30'],
            'email'          => ['required', 'email', 'max:50', Rule::unique($employees, 'email')->ignore($employeeId)->whereNull('deleted_at')],
            'phoneNumber'    => ['required', 'string', 'max:25'],
            'password'       => ['nullable', 'string', 'min:6', 'max:60'],
            'jobName'        => ['required', 'string', 'max:60'],
            'jobDescription' => ['nullable', 'string', 'max:60'],
            'gender'         => ['required', 'in:male,female'],
            'role'           => ['required', 'in:agent,admin,viewer'],
            'officeId'       => [$isAdmin ? 'required' : 'nullable', 'integer', Rule::exists($offices, 'id')->whereNull('deleted_at')],
            'country'        => ['required', 'string', 'max:100'],
            'region'         => ['required', 'string', 'max:100'],
            'city'           => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:500'],
            'isActive'       => ['required', 'in:0,1'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        $data['employeeJobName_en'] = $data['jobName'];
        $data['employeeJobName_ar'] = $data['jobName'];
        $data['job_description_en'] = $data['jobDescription'] ?? '';
        $data['job_description_ar'] = $data['jobDescription'] ?? '';

        unset($data['jobName'], $data['jobDescription']);

        return $data;
    }
}
