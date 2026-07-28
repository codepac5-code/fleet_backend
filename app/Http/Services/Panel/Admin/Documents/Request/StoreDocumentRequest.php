<?php

namespace App\Http\Services\Panel\Admin\Documents\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_required' => $this->boolean('is_required'),
            'status'      => $this->has('status') ? $this->boolean('status') : true,
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
