<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\CancellationReason;

/**
 * Localized active cancellation reasons for an audience. Shared by the rider and
 * driver read endpoints; the label follows the request locale (Accept-Language),
 * the `code` is the stable value the app sends back on cancel.
 */
class CancellationReasonPresenter
{
    public static function forAudience(string $audience): array
    {
        $locale = (app()->getLocale() ?: 'en') === 'ar' ? 'ar' : 'en';

        return CancellationReason::query()
            ->forAudience($audience)
            ->get()
            ->map(fn ($r) => [
                'code' => $r->code,
                'label' => $locale === 'ar' ? $r->label_ar : $r->label_en,
            ])
            ->all();
    }
}
