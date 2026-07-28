<?php

namespace App\Http\Services\Panel\Vehicles\Request;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['fleet_car' => $this->boolean('fleet_car')]);
    }

    public function rules(): array
    {
        $connection = TenantConnection::current();
        $offices = $connection ? $connection . '.offices' : 'offices';
        $drivers = $connection ? $connection . '.drivers' : 'drivers';
        $isAdmin = app(EntityScope::class)->isAdmin();

        return [
            'vehicleBrand'  => ['required', 'string', 'max:100'],
            'model'         => ['required', 'string', 'max:100'],
            'plate'         => ['required', 'string', 'max:50'],
            'modelYear'     => ['required', 'string', 'max:10'],
            'color'         => ['required', 'string', 'max:50'],
            'city'          => ['required', 'string', 'max:100'],
            'licenseNumber' => ['nullable', 'string', 'max:100'],
            'seatsCount'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'driverId'      => ['nullable', 'integer', Rule::exists($drivers, 'id')->whereNull('deleted_at')],
            'officeId'      => [$isAdmin ? 'required' : 'nullable', 'integer', Rule::exists($offices, 'id')->whereNull('deleted_at')],
            'fleet_car'     => ['boolean'],
            'photo'         => ['nullable', 'image', 'max:2048'],
        ];
    }
}
