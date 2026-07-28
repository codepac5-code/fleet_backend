<?php

namespace App\Http\Services\Panel\Vehicles\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_services'   => ['nullable', 'array'],
            'sub_services.*' => ['integer'],
        ];
    }

    public function selected(): array
    {
        return array_map('intval', $this->input('sub_services', []));
    }
}
