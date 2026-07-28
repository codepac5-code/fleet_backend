<?php

namespace App\Http\Services\User\B2B\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class FamilyMemberRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:120'],
            'phone' => [$required, 'string', 'max:32'],
            'type' => ['nullable', 'string', 'max:20'],
            'approvalRequired' => ['nullable', 'boolean'],
            'autoShare' => ['nullable', 'boolean'],
        ];
    }
}
