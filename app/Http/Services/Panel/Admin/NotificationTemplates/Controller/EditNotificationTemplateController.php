<?php

namespace App\Http\Services\Panel\Admin\NotificationTemplates\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Models\NotificationTemplate;
use Illuminate\View\View;

class EditNotificationTemplateController extends Controller
{
    public function __invoke(string $key): View
    {
        $def = TemplateCatalog::get($key);

        if ($def === null) {
            abort(404);
        }

        $override = NotificationTemplate::query()->where('key', $key)->first();

        $current = $override !== null
            ? [
                'subject' => $override->subject_i18n ?? [],
                'body' => $override->body_i18n ?? [],
                'channels' => $override->channels ?? ($def['channels'] ?? ['inapp']),
                'is_active' => (bool) $override->is_active,
            ]
            : [
                'subject' => $def['subject'] ?? [],
                'body' => $def['body'] ?? [],
                'channels' => $def['channels'] ?? ['inapp'],
                'is_active' => false,
            ];

        return view('panel.notification-templates.edit', [
            'key' => $key,
            'def' => $def,
            'current' => $current,
            'overridden' => $override !== null,
        ]);
    }
}
