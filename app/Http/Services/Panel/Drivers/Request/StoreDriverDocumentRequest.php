<?php

namespace App\Http\Services\Panel\Drivers\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'document_id' => ['nullable', 'integer'],
            'file'        => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'expires_at'  => ['nullable', 'date'],
        ];
    }
}
