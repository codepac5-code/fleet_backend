<?php
namespace App\Http\Services\User\ResearchOnDriver\Request;

use App\Http\Core\Request\BaseRequest;

class ResearchOnDriverRequest extends BaseRequest
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
           'orderId' =>['required']
        ];
    }

}
