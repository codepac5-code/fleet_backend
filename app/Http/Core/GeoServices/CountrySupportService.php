<?php

namespace App\Http\Core\GeoServices;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use App\Models\Currency;
use App\Models\InfrastructureNode;
use Illuminate\Support\Facades\Schema;

class CountrySupportService
{
    public function registerCurrency(InfrastructureNode $node): void
    {
        $profile = CountryProfiles::for((string) $node->country_code) ?? [];

        $code = strtoupper(trim((string) ($node->currency_code ?: ($profile['currency_code'] ?? ''))));
        if ($code === '') {
            return;
        }

        $symbol   = $node->currency_symbol ?: ($profile['currency_symbol'] ?? null);
        $name     = $profile['currency_name'] ?? $code;
        $decimals = $profile['decimals'] ?? 2;

        $currency = Currency::query()->firstOrNew(['code' => $code]);

        if (! $currency->exists) {
            $currency->name          = $name;
            $currency->symbol        = $symbol;
            $currency->decimals      = $decimals;
            $currency->exchange_rate = 1;
            $currency->is_active     = true;
            $currency->save();

            return;
        }

        // Never overwrite an admin's edits — only fill in what's missing.
        $dirty = false;
        if (! $currency->symbol && $symbol) {
            $currency->symbol = $symbol;
            $dirty = true;
        }
        if (! $currency->is_active) {
            $currency->is_active = true;
            $dirty = true;
        }
        if ($dirty) {
            $currency->save();
        }
    }

    public function seedProvinces(InfrastructureNode $node, ?array $provinces = null): int
    {
        $provinces = $provinces ?? CountryProfiles::provinces((string) $node->country_code);

        if ($provinces === []) {
            return 0;
        }

        $conn = TenantConnection::current();

        if ($conn === null || ! Schema::connection($conn)->hasTable('cities')) {
            return 0;
        }

        $hasEn         = Schema::connection($conn)->hasColumn('cities', 'en_name');
        $hasCountryId  = Schema::connection($conn)->hasColumn('cities', 'countryId');
        // Some shards were cloned from an older schema without timestamp columns.
        $hasTimestamps = Schema::connection($conn)->hasColumn('cities', 'created_at');

        $existing = [];
        foreach (City::on($conn)->pluck('name') as $name) {
            $existing[mb_strtolower(trim((string) $name))] = true;
        }

        $seeded = 0;

        foreach ($provinces as $p) {
            $ar = is_array($p) ? (string) ($p['ar'] ?? $p['en'] ?? '') : (string) $p;
            $en = is_array($p) ? (string) ($p['en'] ?? $p['ar'] ?? '') : (string) $p;

            $name = trim($ar) !== '' ? trim($ar) : trim($en);
            if ($name === '' || isset($existing[mb_strtolower($name)])) {
                continue;
            }

            $row = new City();
            $row->setConnection($conn);
            $row->timestamps = $hasTimestamps;
            $row->name = $name;
            $row->name_on_google_map = trim($en) !== '' ? trim($en) : $name;
            if ($hasEn) {
                $row->en_name = trim($en) !== '' ? trim($en) : $name;
            }
            if ($hasCountryId) {
                $row->countryId = $node->id;
            }
            $row->save();

            $existing[mb_strtolower($name)] = true;
            $seeded++;
        }

        return $seeded;
    }
}
