<?php
namespace App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Request;

use App\Http\Core\Request\BaseRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateOfficeRequest extends BaseRequest
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
            'officeName' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:offices,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
            'contactNumber' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'serviceIds' => 'required|array',
            // 'status' => 'required|in:0,1',
            // 'walletBalance' => 'required|numeric|min:0',
            // 'limitOrders' => 'required|numeric|min:0',
        ];
    }

    public function messages(){
      return [
        'officeName.required' => __('messages.required', ['attribute' => __('messages.officeName')]),
        'officeName.string' => __('messages.string', ['attribute' => __('messages.officeName')]),
        'logo.image' => __('messages.image', ['attribute' => __('messages.logo')]),
        'logo.mimes' => __('messages.mimes', ['attribute' => __('messages.logo'), 'values' => 'jpeg, png, jpg, gif, svg']),
        'logo.max_size' => __('messages.max_size', ['attribute' => __('messages.logo'), 'max_size' => 2]),
        'email.required' => __('messages.required', ['attribute' => __('messages.email')]),
        'email.email' => __('messages.email', ['attribute' => __('messages.email')]),
        'email.unique' => __('messages.unique', ['attribute' => __('messages.email')]),
        'password.required' => __('messages.required', ['attribute' => __('messages.password')]),
        'password.string' => __('messages.string', ['attribute' => __('messages.password')]),
        'password.min' => __('messages.min', ['attribute' => __('messages.password'), 'min' => 8]),
        'password.confirmed' => __('messages.confirmed', ['attribute' => __('messages.password')]),
        'password.regex' => __('messages.password_complexity'),
        'contactNumber.string' => __('messages.string', ['attribute' => __('messages.contactNumber')]),
        'contactNumber.max' => __('messages.max', ['attribute' => __('messages.contactNumber'), 'max' => 255]),
        'country.required' => __('messages.required', ['attribute' => __('messages.country')]),
        'country.string' => __('messages.string', ['attribute' => __('messages.country')]),
        'country.max' => __('messages.max', ['attribute' => __('messages.country'), 'max' => 255]),
        'city.required' => __('messages.required', ['attribute' => __('messages.city')]),
        'city.string' => __('messages.string', ['attribute' => __('messages.city')]),
        'city.max' => __('messages.max', ['attribute' => __('messages.city'), 'max' => 255]),
        'region.required' => __('messages.required', ['attribute' => __('messages.region')]),
        'region.string' => __('messages.string', ['attribute' => __('messages.region')]),
        'region.max' => __('messages.max', ['attribute' => __('messages.region'), 'max' => 255]),
        'address.string' => __('messages.string', ['attribute' => __('messages.address')]),
        'address.max' => __('messages.max', ['attribute' => __('messages.address'), 'max' => 500]),
        'status.required' => __('messages.required', ['attribute' => __('messages.status')]),
        'status.in' => __('messages.in', ['attribute' => __('messages.status'), 'values' => '0, 1']),
        'walletBalance.required' => __('messages.required', ['attribute' => __('messages.walletBalance')]),
        'walletBalance.numeric' => __('messages.numeric', ['attribute' => __('messages.walletBalance')]),
        'walletBalance.min' => __('messages.min', ['attribute' => __('messages.walletBalance'), 'min' => 0]),
        'limitOrders.required' => __('messages.required', ['attribute' => __('messages.limitOrders')]),
        'limitOrders.numeric' => __('messages.numeric', ['attribute' => __('messages.limitOrders')]),
        'limitOrders.min' => __('messages.limit_orders', ['attribute' => __('messages.limitOrders')]),
    ];}
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }



    

    // 'password_complexity' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
    // 'password.regex' => __('messages.password_complexity'),
    // 'password_complexity' => 'كلمة المرور يجب أن تحتوي على حرف كبير واحد على الأقل، حرف صغير واحد على الأقل، رقم واحد على الأقل، ورمز خاص واحد على الأقل.',


}
