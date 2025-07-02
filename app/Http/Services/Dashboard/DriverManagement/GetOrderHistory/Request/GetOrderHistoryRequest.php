<?php
namespace App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Request;

use App\Http\Core\Request\BaseRequest;

class GetOrderHistoryRequest extends BaseRequest
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
            'startDate' => ['required' , 'string' ,  'date_format:Y-m-d'],
            'endDate' => ['required' , 'string' , 'date_format:Y-m-d'],
            'driverId' => ['required' ]

        ];
    }

}
