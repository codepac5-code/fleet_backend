<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Request;

use App\Http\Core\Const\Options\Guard;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Request\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\Http\Middleware\CheckCredentials;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
class LoginToDashboardAsOfficeRequest extends BaseRequest
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
            'email'       =>    'required|email|exists:offices,email',
            'password'    =>    'required|string',
            // 'account_type' =>    'required'
        ];
    }



    

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        // $this->ensureIsNotRateLimited();
        //guard('guardName')->
        if(! Auth::guard(Guard::$Office)->attempt($this->only('email', 'password'), $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey()); 

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if($user->status == 0) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('auth.account_inactive')
            ]);
        }
        //  RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
                'email'    => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }



    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, redirect()->back()
        ->withErrors($validator)
        ->withInput());
    }
}
