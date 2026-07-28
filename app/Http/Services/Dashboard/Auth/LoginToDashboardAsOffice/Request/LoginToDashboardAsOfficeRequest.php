<?php
namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Request;

use App\Http\Core\Const\Options\Guard;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Request\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class LoginToDashboardAsOfficeRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
            'role'     => 'required|string|in:employee,manager',
            'region'   => 'required',
        ];
    }

    public function authenticate()
    {
        $role = $this->input('role');

        $guard = null;
        if ($role === 'employee') {
            $guard = Guard::$Employee;
        } elseif ($role === 'manager') {
            $guard = Guard::$Office;
        } else {
            throw ValidationException::withMessages([
                'role' => __('نوع الحساب غير صالح.'),
            ]);
        }

        if (!Auth::guard($guard)->attempt($this->only('email', 'password'), $this->filled('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::guard($guard)->user();

        if ($user->status == 0) {
            Auth::guard($guard)->logout();
            throw ValidationException::withMessages([
                'email' => __('auth.account_inactive'),
            ]);
        }

        // RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

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
