<?php
namespace App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class PushNotificationRequest extends BaseRequest
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
            'title_ar'         =>['required','string'],
            'title_en'         =>['required','string'],
            'body_en'         =>['required','string'],
            'body_ar'   =>      ['required','string'],
            // 'image'         =>['required'],
            'is_type'       =>['required']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }
}
