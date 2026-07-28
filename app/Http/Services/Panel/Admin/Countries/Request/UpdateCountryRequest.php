<?php

namespace App\Http\Services\Panel\Admin\Countries\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->filled('country_code')) {
            $merge['country_code'] = strtoupper(trim($this->input('country_code')));
        }

        if ($this->filled('currency_code')) {
            $merge['currency_code'] = strtoupper(trim($this->input('currency_code')));
        }

        $merge['is_active'] = $this->boolean('is_active');

        $this->merge($merge);
    }

    public function rules(): array
    {
        $id = $this->route('node');

        return [
            'name'            => ['required', 'string', 'max:120'],
            'country_code'    => ['required', 'string', 'size:2', Rule::unique('infrastructure_nodes', 'country_code')->ignore($id)],
            'city'            => ['nullable', 'string', 'max:120'],
            'lat'             => ['nullable', 'numeric', 'between:-90,90'],
            'lng'             => ['nullable', 'numeric', 'between:-180,180'],
            'currency_code'   => ['nullable', 'string', 'size:3'],
            'currency_symbol' => ['nullable', 'string', 'max:8'],

            'db_host'         => ['required', 'string', 'max:191'],
            'db_port'         => ['required', 'integer', 'between:1,65535'],
            'db_name'         => ['required', 'string', 'max:191'],
            'db_user'         => ['required', 'string', 'max:191'],
            'db_pass'         => ['nullable', 'string', 'max:191'],

            'redis_host'      => ['nullable', 'string', 'max:191'],
            'redis_db'        => ['nullable', 'integer', 'between:0,63'],
            'redis_prefix'    => ['nullable', 'string', 'max:64'],

            'is_active'       => ['boolean'],
        ];
    }
}
