<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class SavePlaceRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:32'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:16'],
            'title' => ['sometimes', 'nullable', 'string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
