<?php

namespace App\Http\Services\Panel\Admin\Currencies\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Validation\Rule;

class StoreCurrencyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        return [
            'code'          => ['required', 'string', 'max:10', Rule::unique('currencies', 'code')],
            'name'          => ['required', 'string', 'max:100'],
            'symbol'        => ['nullable', 'string', 'max:10'],
            'decimals'      => ['nullable', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'is_default'    => ['nullable', 'boolean'],
        ];
    }
}
