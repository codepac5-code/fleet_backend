<?php

namespace App\Http\Services\Panel\Admin\Offices\Request;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfficeRequest extends FormRequest
{
    use NormalizesWorkingHours;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_verified' => $this->boolean('is_verified'),
            'is_monitored' => $this->boolean('is_monitored'),
            'working_hours' => $this->normalizedWorkingHours(),
        ]);
    }

    public function rules(): array
    {
        $connection = TenantConnection::current();
        $table = $connection ? $connection . '.offices' : 'offices';

        return [
            'officeName'    => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:191', Rule::unique($table, 'email')->whereNull('deleted_at')],
            'password'      => ['required', 'string', 'min:6', 'max:60'],
            'contactNumber' => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:100'],
            'region'        => ['nullable', 'string', 'max:100'],
            'city'          => ['nullable', 'string', 'max:100'],
            'address'       => ['nullable', 'string', 'max:500'],
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lng'           => ['nullable', 'numeric', 'between:-180,180'],
            'limitOrders'   => ['nullable', 'integer', 'min:0'],
            'status'        => ['required', 'in:0,1'],
            'is_verified'   => ['boolean'],
            'is_monitored'  => ['boolean'],
            'working_hours' => ['nullable', 'array'],
            'working_hours.*.closed' => ['boolean'],
            'working_hours.*.open' => ['nullable', 'date_format:H:i'],
            'working_hours.*.close' => ['nullable', 'date_format:H:i'],
        ];
    }
}
