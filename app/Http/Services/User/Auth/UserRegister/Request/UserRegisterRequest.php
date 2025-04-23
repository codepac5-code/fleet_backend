<?php
namespace App\Http\Services\User\Auth\UserRegister\Request;

use App\Http\Core\Request\BaseRequest;

class UserRegisterRequest extends BaseRequest
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
            'password'=>['required' ,"string","min:8"],
            'firstName'=>['required', 'string' , 'max:25', 'min:3'],
            'lastName'=> ['required',  'string', 'max:25', 'min:3'],
            'phoneNumber'=>['required', 'numeric', 'digits:10'],
        ];
    }

}
