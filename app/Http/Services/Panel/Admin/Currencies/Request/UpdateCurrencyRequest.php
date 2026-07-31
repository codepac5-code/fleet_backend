<?php

namespace App\Http\Services\Panel\Admin\Currencies\Request;

use App\Http\Core\Request\BaseRequest;

class UpdateCurrencyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'symbol'        => ['nullable', 'string', 'max:10'],
            'decimals'      => ['nullable', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            'is_default'    => ['nullable', 'boolean'],
        ];
    }
}
