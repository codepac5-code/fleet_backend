<?php
namespace App\Http\Services\User\GetSubService\Request;

use App\Http\Core\Request\BaseRequest;

class GetSubServiceRequest extends BaseRequest
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
            'serviceId' => ["required" , "integer" , 'exists:services,id'],
            'start' => ['required' , 'string'],
            'destination' => ['required' , 'string'],
            'kmEst' => ['required' , 'numeric'],
            'timeEst' => ['required' , 'numeric']
        ];
    }
    protected function passedValidation()
    {
        // قم بتنفيذ العملية التي ترغب بها بعد التحقق، مثل تعديل قيمة
        $this->merge([
            'kmEst' => floatval($this->kmEst),
            'timeEst' => floatval($this->timeEst),
        ]);
    }


}
