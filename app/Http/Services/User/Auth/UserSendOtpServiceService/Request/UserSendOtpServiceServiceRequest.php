<?php
namespace App\Http\Services\User\Auth\UserSendOtpServiceService\Request;

use App\Http\Core\Request\BaseRequest;

class UserSendOtpServiceServiceRequest extends BaseRequest
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
        return [
            'phoneNumber'=>['required',  'numeric'],
            'dialCode'=>'required'
        ];
    }

}
