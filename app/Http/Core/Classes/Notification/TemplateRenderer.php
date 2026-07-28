<?php

namespace App\Http\Core\Classes\Notification;

use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Models\NotificationTemplate;
use RuntimeException;

class TemplateRenderer
{
    public function render(string $key, string $locale, array $vars): array
    {
        $template = $this->load($key);

        if (!$template) {
            throw new RuntimeException('unknown notification template: ' . $key);
        }

        $loc = $locale;
        $body = $template['body'][$loc] ?? $template['body'][TemplateCatalog::DEFAULT_LOCALE] ?? null;
        $subject = $template['subject'][$loc] ?? $template['subject'][TemplateCatalog::DEFAULT_LOCALE] ?? null;

        if ($body === null) {
            throw new RuntimeException('template ' . $key . ' has no body');
        }

        return [
            'channels' => $template['channels'] ?? ['inapp'],
            'subject' => $subject !== null ? $this->interpolate($subject, $vars) : null,
            'body' => $this->interpolate($body, $vars),
        ];
    }

    private function load(string $key): ?array
    {
        $row = NotificationTemplate::query()->where('key', $key)->where('is_active', true)->first();

        if ($row) {
            return [
                'channels' => $row->channels ?? ['inapp'],
                'subject' => $row->subject_i18n ?? [],
                'body' => $row->body_i18n ?? [],
            ];
        }

        return TemplateCatalog::get($key);
    }

    private function interpolate(string $text, array $vars): string
    {
        foreach ($vars as $k => $v) {
            if (is_scalar($v)) {
                $text = str_replace('{' . $k . '}', (string) $v, $text);
            }
        }

        return $text;
    }
}
