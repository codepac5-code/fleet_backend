<?php

namespace App\Http\Core\GeoServices;

use App\Models\InfrastructureNode;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Decides which country a free-text country name refers to.
 *
 * Two countries can share ONE database (Saudi Arabia and Qatar both live in
 * `fleet`), so a row found "in both" is not really ambiguous — the office it
 * belongs to names its own country in plain text (`QATAR`). This matches that
 * text against everything a country answers to: its ISO code, its shard node
 * name, and its Arabic/English names from the `countries` table.
 *
 * Returns null when nothing matches OR when more than one candidate does: a
 * wrong attribution is worse than an unattributed row.
 */
class CountryNameResolver
{
    /** country code => the names it answers to. */
    private ?array $names = null;

    public function match(?string $countryName, array $candidateCodes): ?string
    {
        $needle = $this->normalise((string) $countryName);

        if ($needle === '' || $candidateCodes === []) {
            return null;
        }

        $matches = [];

        foreach ($candidateCodes as $code) {
            $code = strtolower((string) $code);

            foreach ($this->namesFor($code) as $name) {
                if ($this->normalise($name) === $needle) {
                    $matches[] = $code;
                    break;
                }
            }
        }

        $matches = array_values(array_unique($matches));

        return count($matches) === 1 ? $matches[0] : null;
    }

    public function namesFor(string $countryCode): array
    {
        $this->load();

        return $this->names[strtolower($countryCode)] ?? [strtolower($countryCode)];
    }

    private function load(): void
    {
        if ($this->names !== null) {
            return;
        }

        $this->names = [];

        try {
            foreach (InfrastructureNode::query()->where('type', 'country')->get() as $node) {
                $code = strtolower((string) $node->country_code);
                $this->names[$code] = array_filter([$code, (string) $node->name]);
            }
        } catch (Throwable $e) {
        }

        try {
            foreach (DB::connection('global')->table('countries')->get(['iso2', 'name', 'en_name']) as $row) {
                $code = strtolower((string) $row->iso2);
                $this->names[$code] = array_values(array_filter(array_unique(array_merge(
                    $this->names[$code] ?? [$code],
                    [(string) $row->name, (string) $row->en_name]
                ))));
            }
        } catch (Throwable $e) {
        }
    }

    private function normalise(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim(mb_strtolower($value))) ?? '';
    }
}
