<?php

namespace App\Http\Services\Panel\Shared\Notifications\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Notifications\NotificationFeed;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class NotificationsPageController extends Controller
{
    public function __invoke(EntityScope $scope, NotificationFeed $feed): View
    {
        $items = $feed->build(30);
        $feed->markAllRead();

        return view('panel.notifications.index', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'notifications' => $items,
        ]);
    }
}
