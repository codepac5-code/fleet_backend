<?php

namespace App\Http\Services\Panel\Services\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['travel_service' => $this->boolean('travel_service')]);
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:150'],
            'title_en'       => ['nullable', 'string', 'max:150'],
            'description'    => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
            'status'         => ['required', 'in:0,1'],
            'travel_service' => ['boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'badge'          => ['nullable', 'string', 'max:32'],
            'icon'           => ['nullable', 'string', 'max:64'],
            'image'          => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function payload(): array
    {
        $data = $this->safe()->except(['image']);
        $data['title_en'] = $data['title_en'] ?: $data['title'];

        return $data;
    }
}
