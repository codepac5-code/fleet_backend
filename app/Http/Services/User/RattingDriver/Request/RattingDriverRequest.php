<?php
namespace App\Http\Services\User\RattingDriver\Request;

use App\Http\Core\Request\BaseRequest;

class RattingDriverRequest extends BaseRequest
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
            'description' => ['nullable','string'],
            'rating' => ['required' ,  'regex:/^\d+(\.\d{1,2})?$/' ],
            'orderId' => ['required' ,'integer','exists:bookings,id'],
        ];
    }

}
