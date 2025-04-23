<?php
namespace App\Http\Services\User\UserAddressManagement\AddAddress\Request;

use App\Http\Core\Request\BaseRequest;

class AddAddressRequest extends BaseRequest
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
            'address'=>['required', 'string'],
            'town'=>['required', 'string'],
            'addressName' => ['required', 'string'],
            'lang' => ['required', 'string'],
            'lat' => ['required', 'string' ],
            'phone' => ['required', 'numeric', 'digits:10'],
            'description' => ['required','string'],
        ];
    }

}
