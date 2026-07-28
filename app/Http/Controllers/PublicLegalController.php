<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated legal copy for the apps and the website. Global
 * (platform-wide) — the same terms/privacy for every country, localised.
 */
class PublicLegalController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $locale = strtolower((string) $request->query('locale', app()->getLocale()));
        $locale = in_array($locale, ['en', 'ar'], true) ? $locale : 'en';

        return response()->json([
            'locale' => $locale,
            'terms' => (string) SiteSetting::val('legal_terms_' . $locale, ''),
            'privacy' => (string) SiteSetting::val('legal_privacy_' . $locale, ''),
        ]);
    }
}
