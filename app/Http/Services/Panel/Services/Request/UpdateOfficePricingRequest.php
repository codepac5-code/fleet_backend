<?php

namespace App\Http\Services\Panel\Services\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prices'               => ['nullable', 'array'],
            'prices.*.openPrice'   => ['nullable', 'numeric', 'min:0'],
            'prices.*.kmPrice'     => ['nullable', 'numeric', 'min:0'],
            'prices.*.minutePrice' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function rows(): array
    {
        return $this->input('prices', []);
    }
}
