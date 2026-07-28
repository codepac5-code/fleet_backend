<?php

namespace App\Http\Services\Panel\Admin\Offices\Request;

use Illuminate\Foundation\Http\FormRequest;

class AddBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'note'   => ['nullable', 'string', 'max:200'],
        ];
    }
}
