<?php
namespace App\Http\Services\User\ProfileManagement\EditeProfile\Request;

use App\Http\Core\Request\BaseRequest;

class EditeProfileRequest extends BaseRequest
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
            'firstName'=>['required', 'string' , 'max:25', 'min:3'],
            'lastName'=> ['required',  'string', 'max:25', 'min:3'],
            'gender' => ['nullable','string','in:male,female']
        ];
    }

}
