<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Request;

use App\Http\Core\Request\BaseRequest;

class UserCheckOtpServiceRequest extends BaseRequest
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
            'code'          =>  ['required' ,"string","min:6","max:6"],
            'phoneNumber'   =>  ['required' , 'numeric', 'digits:10'],
            'userId'        =>  ['required' ,"integer"],
        ];
    }

}
