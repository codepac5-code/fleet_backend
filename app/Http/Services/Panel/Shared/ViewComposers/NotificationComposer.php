<?php

namespace App\Http\Services\Panel\Shared\ViewComposers;

use App\Http\Services\Panel\Shared\Notifications\NotificationFeed;
use Illuminate\View\View;

class NotificationComposer
{
    public function __construct(private NotificationFeed $feed) {}

    public function compose(View $view): void
    {
        $items = $this->feed->build(8);

        $view->with('panelNotifications', $items)
            ->with('panelNotifUnread', $this->feed->unreadCount($items));
    }
}
