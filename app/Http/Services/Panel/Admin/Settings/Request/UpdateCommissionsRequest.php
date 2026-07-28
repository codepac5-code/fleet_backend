<?php

namespace App\Http\Services\Panel\Admin\Settings\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fleet_commission_value_with_driver' => ['required', 'numeric', 'min:0', 'max:100'],
            'fleet_commission_value_with_office' => ['required', 'numeric', 'min:0', 'max:100'],
            'office_commission_value'            => ['required', 'numeric', 'min:0', 'max:100'],
            'driver_commission_value'            => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
