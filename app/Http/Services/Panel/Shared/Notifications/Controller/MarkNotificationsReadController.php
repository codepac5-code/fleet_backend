<?php

namespace App\Http\Services\Panel\Shared\Notifications\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Notifications\NotificationFeed;
use Illuminate\Http\RedirectResponse;

class MarkNotificationsReadController extends Controller
{
    public function __invoke(NotificationFeed $feed): RedirectResponse
    {
        $feed->markAllRead();

        return back();
    }
}
