<?php

namespace App\Http\Services\Panel\RiderSupport\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\RiderSupportService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class RiderSupportThreadPageController extends Controller
{
    public function __invoke(EntityScope $scope, int $ticket, RiderSupportService $support): View
    {
        try {
            $thread = $scope->isAdmin()
                ? $support->thread($ticket)
                : $support->officeThread((int) $scope->officeId(), $ticket);
        } catch (DomainException $e) {
            abort(404);
        }

        return view('panel.rider-support.thread', [
            'entity'  => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'ticket'  => $thread,
        ]);
    }
}
