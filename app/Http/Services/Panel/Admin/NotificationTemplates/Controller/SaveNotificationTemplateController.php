<?php

namespace App\Http\Services\Panel\Admin\NotificationTemplates\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Models\NotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveNotificationTemplateController extends Controller
{
    private const CHANNELS = ['inapp', 'push', 'email'];

    public function __invoke(string $key, Request $request): RedirectResponse
    {
        if (TemplateCatalog::get($key) === null) {
            abort(404);
        }

        $data = $request->validate([
            'subject_en' => ['nullable', 'string', 'max:255'],
            'subject_ar' => ['nullable', 'string', 'max:255'],
            'body_en' => ['required', 'string', 'max:2000'],
            'body_ar' => ['nullable', 'string', 'max:2000'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', 'in:' . implode(',', self::CHANNELS)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $channels = array_values(array_intersect(self::CHANNELS, $data['channels'] ?? []));

        NotificationTemplate::query()->updateOrCreate(
            ['key' => $key],
            [
                'subject_i18n' => array_filter(['en' => $data['subject_en'] ?? '', 'ar' => $data['subject_ar'] ?? ''], fn ($v) => $v !== ''),
                'body_i18n' => array_filter(['en' => $data['body_en'] ?? '', 'ar' => $data['body_ar'] ?? ''], fn ($v) => $v !== ''),
                'channels' => $channels !== [] ? $channels : ['inapp'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]
        );

        return redirect()
            ->route('panel.admin.notification-templates.index')
            ->with('status', textByLanguage('تم حفظ القالب', 'Template saved'));
    }
}
