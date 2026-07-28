<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class CreateOrUpdateSubServiceRequest extends BaseRequest
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

    public function rules(){
        $rules = [
            'name'          => 'required|string|max:255|regex:/^[\p{Arabic}\s]+$/u',
            'name_en'       => 'required|string|max:255|regex:/^[A-Za-z0-9\s]*$/',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'        => 'required|boolean',
            'description'   => 'required|string|regex:/^[\p{Arabic}\s]+$/u',
            'description_en'=> 'required|string|regex:/^[A-Za-z0-9\s]*$/',
            'serviceId'     => 'required|exists:services,id',
            'id'            => 'nullable|numeric',
            'current_image' => 'nullable',
        ];

        $serviceId = $this->input('serviceId');
        $service = \App\Models\Service::find($serviceId);
        $isTravelService = $service && $service->travel_service;

        if ($isTravelService) {
            $rules['routes'] = 'required|array|min:1';
            $rules['routes.*.departureCity'] = 'required|string';
            $rules['routes.*.arrivalCity'] = 'required|string';
            $rules['routes.*.tripPrice'] = 'required|numeric|min:0';
        } else {
            $rules['openPrice'] = 'required|numeric|min:0';
            $rules['kmPrice'] = 'required|numeric|min:0';
            $rules['minutePrice'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    // public function rules()
    // {
    //     return [
    //         'name'          => 'required|string|max:255|regex:/^[\p{Arabic}\s]+$/u',
    //         'name_en'       => 'required|string|max:255|regex:/^[A-Za-z0-9\s]*$/',
    //         'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         'status'        => 'required|boolean',
    //         'description'   => 'required|string|regex:/^[\p{Arabic}\s]+$/u',
    //         'description_en' => 'required|string|regex:/^[A-Za-z0-9\s]*$/',
    //         'openPrice'     => 'required|numeric|min:0',
    //         'kmPrice'       => 'required|numeric|min:0',
    //         'minutePrice'   => 'required|numeric|min:0',
    //         'serviceId'     => 'required',
    //         'id'            => 'nullable|numeric',
    //         'current_image' => 'nullable',

    //         'routes' => 'sometimes|array|min:1',
    //         'routes.*.departureCity' => 'sometimes|string',
    //         'routes.*.arrivalCity' => 'sometimes|string',
    //         'routes.*.tripPrice' => 'sometimes|numeric|min:0',
    //     ];
    // }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }


    public function messages(){
        return [
            'name_en.regex' => __('messages.regex_name_en'),
            'name.regex' => __('messages.regex_name_ar'),
            'description.regex' => __('messages.regex_name_en'),
            'description_en.regex' => __('messages.regex_name_ar'),
         ];
    }

}
