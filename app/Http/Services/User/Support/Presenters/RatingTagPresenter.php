<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\RatingTag;

/**
 * Localized active rating tags for an audience, optionally narrowed to the star
 * rating being given. Shared by the rider and driver read endpoints; the `code`
 * is what the app sends back in `tags[]`.
 */
class RatingTagPresenter
{
    public static function forAudience(string|array $audience, ?int $stars = null): array
    {
        $locale = (app()->getLocale() ?: 'en') === 'ar' ? 'ar' : 'en';

        return RatingTag::query()
            ->forAudience($audience, $stars)
            ->get()
            ->map(fn ($t) => [
                'code' => $t->code,
                'label' => $locale === 'ar' ? $t->label_ar : $t->label_en,
                'audience' => $t->audience,
                'starsMin' => $t->stars_min,
                'starsMax' => $t->stars_max,
            ])
            ->all();
    }
}
