<?php
namespace App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class CreateOrUpdateCouponRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return
        [
            "id"=>'nullable|numeric',
            "code"=> 'required|string|unique:coupons,code',
            "discounType"=> 'required|string',
            "discount"=> 'required|string',
            "expireDate" =>'required|date',
            "serviceIds"=>'required',
            "isActive"=>'nullable|boolean',
            "limit"=>'required|numeric'
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }

}
