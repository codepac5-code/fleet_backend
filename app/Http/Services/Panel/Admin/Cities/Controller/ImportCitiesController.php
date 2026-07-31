<?php

namespace App\Http\Services\Panel\Admin\Cities\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\CountryProfiles;
use App\Http\Core\GeoServices\CountrySupportService;
use App\Http\Core\GeoServices\ShardContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportCitiesController extends Controller
{
    public function __invoke(Request $request, CountrySupportService $support): RedirectResponse
    {
        $node = ShardContext::current();

        if ($node === null) {
            return back()->with('error', textByLanguage('اختر الدولة أولاً', 'Select a country first'));
        }

        // "Fill from the built-in list" — one click seeds the bundled provinces for
        // this country (Syria, Qatar, USA, Saudi, …). For anything else, the admin
        // pastes a list: one province per line, optionally "Arabic | English".
        if ($request->boolean('use_bundled')) {
            $provinces = CountryProfiles::provinces((string) $node->country_code);
        } else {
            $provinces = $this->parse((string) $request->input('provinces', ''));
        }

        if ($provinces === []) {
            return back()->with('error', textByLanguage('لا توجد محافظات للإضافة', 'Nothing to import'));
        }

        $count = $support->seedProvinces($node, $provinces);

        return back()->with('status', textByLanguage(
            "تمت إضافة {$count} محافظة",
            "Imported {$count} provinces"
        ));
    }

    private function parse(string $raw): array
    {
        $out = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s*[|,]\s*/', $line, 2);
            $out[] = ['ar' => $parts[0], 'en' => $parts[1] ?? $parts[0]];
        }

        return $out;
    }
}
