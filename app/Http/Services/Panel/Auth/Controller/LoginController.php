<?php

namespace App\Http\Services\Panel\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Auth\Logic\LoginInput;
use App\Http\Services\Panel\Auth\Logic\LoginLogic;
use App\Http\Services\Panel\Auth\Request\LoginRequest;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $input = new LoginInput($request->validated());

        return (new LoginLogic($input))->execute();
    }
}
