<?php
namespace App\Http\Services\Driver\SendJobApplication\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SendJobApplicationRequest extends BaseRequest
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
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:driver_job_applications,phoneNumber',
            'password' => 'required|string|min:8',
        //  'office' => 'required|exists:offices,id',
            'brand' => 'required|string',
            'model' => 'required|string|max:100',
            'year' => 'required|digits:4|string',
            'color' => 'required|string|max:50',
            'plateNumber' => 'required|string|max:20',
    
            'profileImage' => 'required|image',
            'idFrontImage' => 'required|image',
            'idBackImage' => 'required|image',
            'licenseFrontImage' => 'required|image',
            'licenseBackImage' => 'required|image',
            'mechanicalImage' => 'required|image',
            'frontCarImage' => 'required|image',
            'backCarImage' => 'required|image',
            'rightCarImage' => 'required|image',
            'leftCarImage' => 'required|image',
            'insideCarImage' => 'required|image',
            'frontSeatsImage' => 'required|image',
            'backSeatsImage' => 'required|image',
    
            // 'optionalVideo' => 'nullable|file|mimetypes:video/mp4,video/mpeg|max:20000',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }
    

}
