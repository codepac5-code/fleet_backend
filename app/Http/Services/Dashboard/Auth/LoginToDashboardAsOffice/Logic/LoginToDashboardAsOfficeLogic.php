<?php

namespace App\Http\Services\Dashboard\Auth\LoginToDashboardAsOffice\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LoginToDashboardAsOfficeLogic implements Service
{
    private LoginToDashboardAsOfficeInput $input;

    public function __construct(LoginToDashboardAsOfficeInput $input)
    {
        $this->input = $input;
    }

    public function execute(): ResponseModel|JsonResponse|View|RedirectResponse
    {
        $credentials = [
            'email' => $this->input->getEmail(),
            'password' => $this->input->getPassword(),
        ];

        $node = InfrastructureNode::where('type', 'country')
            ->where('id', $this->input->getRegion())
            ->first();

        if (!$node) {
            return redirect()->back()
                ->withErrors(['region' => 'المنطقة المختارة غير مدعومة']);
        }

        // Activate the shard for THIS login attempt — not just the context: the
        // office/employee tables live on the shard, so the `dynamic` connection
        // must be repointed at this country's DB before `authenticate()` queries
        // them. `ShardContext::set()` alone left `dynamic` unconfigured → the
        // "Database hosts array is empty" failure on the employees lookup.
        // NOTE: `active_shard_id` is stored AFTER logoutAuthUser() — that call
        // invalidates the session and would otherwise wipe the marker, leaving
        // the panel to resolve office #1 on the wrong shard (every shard has an
        // id=1). The connection is activated here (before authenticate); only the
        // session marker waits.
        ShardManager::activate($node);

        logoutAuthUser();

        session(['active_shard_id' => $node->id]);

        $guard = $this->input->getGuardName();

        if (!$guard) {
            return redirect()->back()
                ->withErrors(['role' => 'نوع الحساب غير صالح أو غير معرف']);
        }

        if (!authenticate($credentials, $this->input->getRemember(), $guard)) {
            return redirect()->back()
                ->withErrors(['password' => 'الرجاء التأكد من صحة المعلومات المدخلة و المحاولة مجدداً']);
        }

        session()->regenerate();

        $message = 'welcome to fleet';

        if (checkGuard(Guard::$Employee)) {
            $message = 'مرحباً بك في فلييت';
            return redirect(route('booking.index'))->withSuccess($message);
        }

        return redirect(route('home'))->withSuccess($message);
    }
}
