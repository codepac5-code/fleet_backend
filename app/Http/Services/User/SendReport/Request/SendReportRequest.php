<?php
namespace App\Http\Services\User\SendReport\Request;

use App\Http\Core\Request\BaseRequest;

class SendReportRequest extends BaseRequest
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
            'image' => ['sometimes','image','mimes:jpeg,png,jpg','max:2048'],
            'subject'=>['sometimes','string'],
            'reason'=>['sometimes','string'],
            'id'=>['sometimes','string'],
            'description'=>['sometimes','string'],
        ];
    }

}
