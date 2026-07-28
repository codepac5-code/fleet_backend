<?php

namespace App\Http\Services\User\Booking\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class ChangeOfficeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'office_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
