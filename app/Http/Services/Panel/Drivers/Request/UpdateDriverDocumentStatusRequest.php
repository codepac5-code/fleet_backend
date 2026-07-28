<?php

namespace App\Http\Services\Panel\Drivers\Request;

use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverDocumentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(DocumentStatus::all())],
            'note'   => ['nullable', 'string', 'max:500'],
        ];
    }
}
