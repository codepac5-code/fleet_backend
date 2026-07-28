<?php

namespace App\Http\Services\Panel\Admin\NotificationTemplates\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Models\NotificationTemplate;
use Illuminate\View\View;

class NotificationTemplatesPageController extends Controller
{
    public function __invoke(): View
    {
        // Templates are platform-wide (NotificationTemplate is on `global`): the
        // message wording is the same for every country, only the locale differs.
        $overrides = NotificationTemplate::query()->get()->keyBy('key');

        $templates = collect(TemplateCatalog::TEMPLATES)->map(function (array $def, string $key) use ($overrides) {
            $override = $overrides->get($key);

            return [
                'key' => $key,
                'subject' => $def['subject']['en'] ?? $key,
                'channels' => $def['channels'] ?? ['inapp'],
                'overridden' => $override !== null,
                'is_active' => $override !== null ? (bool) $override->is_active : false,
            ];
        })->values();

        return view('panel.notification-templates.index', [
            'templates' => $templates,
        ]);
    }
}
