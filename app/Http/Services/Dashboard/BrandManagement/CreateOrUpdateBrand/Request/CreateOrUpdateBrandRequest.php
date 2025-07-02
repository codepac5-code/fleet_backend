<?php
namespace App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class CreateOrUpdateBrandRequest extends BaseRequest
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
            
            'name'          => 'required|string|max:255|regex:/^[\p{Arabic}\s]+$/u',
            'name_en'       => 'required|string|max:255|regex:/^[A-Za-z0-9\s]*$/', 
            'description' => 'nullable|string|max:200|regex:/^[\p{Arabic}\s]+$/u',
            'description_en' => 'nullable|string|max:200|regex:/^[A-Za-z0-9\s]*$/',
            'image'=>['required'],
        ];
    }


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
