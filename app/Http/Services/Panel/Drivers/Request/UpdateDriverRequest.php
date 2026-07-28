<?php

namespace App\Http\Services\Panel\Drivers\Request;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'car_owner'   => $this->boolean('car_owner'),
            'free_driver' => $this->boolean('free_driver'),
        ]);
    }

    public function rules(): array
    {
        $connection = TenantConnection::current();
        $drivers = $connection ? $connection . '.drivers' : 'drivers';
        $offices = $connection ? $connection . '.offices' : 'offices';
        $isAdmin = app(EntityScope::class)->isAdmin();
        $driverId = (int) $this->route('driver');

        return [
            'firstName'   => ['required', 'string', 'max:30'],
            'lastName'    => ['required', 'string', 'max:30'],
            'dialCode'    => ['required', 'string', 'max:8'],
            'phoneNumber' => ['required', 'string', 'max:10', Rule::unique($drivers, 'phoneNumber')->ignore($driverId)->whereNull('deleted_at')],
            'password'    => ['nullable', 'string', 'min:6', 'max:60'],
            'gender'      => ['nullable', 'in:male,female'],
            'officeId'    => [$isAdmin ? 'required' : 'nullable', 'integer', Rule::exists($offices, 'id')->whereNull('deleted_at')],
            'country'     => ['required', 'string', 'max:100'],
            'region'      => ['required', 'string', 'max:100'],
            'city'        => ['required', 'string', 'max:100'],
            'address'     => ['required', 'string', 'max:500'],
            'car_owner'   => ['boolean'],
            'free_driver' => ['boolean'],
            'isActive'    => ['required', 'in:0,1'],
        ];
    }
}
