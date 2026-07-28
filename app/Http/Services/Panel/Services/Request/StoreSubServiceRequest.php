<?php

namespace App\Http\Services\Panel\Services\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_travel' => $this->boolean('is_travel')]);
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'name_en'        => ['nullable', 'string', 'max:150'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'openPrice'      => ['required', 'numeric', 'min:0'],
            'kmPrice'        => ['required', 'numeric', 'min:0'],
            'minutePrice'    => ['required', 'numeric', 'min:0'],
            'status'         => ['required', 'in:0,1'],
            'is_travel'      => ['boolean'],
            'base_fare'      => ['nullable', 'numeric', 'min:0'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'badge'          => ['nullable', 'string', 'max:32'],
            'icon'           => ['nullable', 'string', 'max:64'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function payload(): array
    {
        $data = $this->safe()->except(['image']);
        $data['name_en'] = $data['name_en'] ?: $data['name'];

        return $data;
    }
}
