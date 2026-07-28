<?php

namespace App\Http\Services\Panel\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Contracts\View\View;

class LoginPageController extends Controller
{
    public function __invoke(): View
    {
        $countries = InfrastructureNode::where('type', 'country')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('panel.auth.login', compact('countries'));
    }
}
