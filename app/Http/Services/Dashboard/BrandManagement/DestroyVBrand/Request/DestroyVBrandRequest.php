<?php
namespace App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Request;

use App\Http\Core\Request\BaseRequest;

class DestroyVBrandRequest extends BaseRequest
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
           'vbrandId'=>'required'
        ];
    }

}
