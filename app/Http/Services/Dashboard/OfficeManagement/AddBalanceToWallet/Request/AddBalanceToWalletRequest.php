<?php

namespace App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Request;

use App\Http\Core\Request\BaseRequest;

class AddBalanceToWalletRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_id' => 'required|exists:offices,id',
            'amount'    => 'required|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'office_id.required' => __('messages.office_required'),
            'office_id.exists'   => __('messages.office_not_found'),
            'amount.required'    => __('messages.invalid_amount'),
            'amount.numeric'     => __('messages.invalid_amount'),
            'amount.min'         => __('messages.invalid_amount'),
        ];
    }
}
