<?php

namespace App\Http\Services\Panel\Admin\Documents\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_required' => $this->boolean('is_required'),
            'status'      => $this->boolean('status'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'is_required' => ['boolean'],
            'status'      => ['boolean'],
        ];
    }
}
