<?php

namespace App\Console\Commands;

use App\Http\Core\GeoServices\CountryNameResolver;
use App\Http\Core\GeoServices\ShardRunner;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Complaint;
use App\Models\Driver;
use App\Models\LostItem;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\SavedPlace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Attributes legacy rider-support rows (complaints, lost items) to a country and
 * — for complaints — to the office they concern. Rows predate the discriminators
 * so they are invisible under any single-country view until backfilled. A row is
 * attributed only when its booking is found in EXACTLY one shard; ambiguous or
 * booking-less rows are left alone rather than guessed.
 */
class BackfillSupportScope extends Command
{
    protected $signature = 'fleet:support-backfill
        {--dry-run : report what would change without writing}
        {--infer-from-rider : also attribute trip-less complaints from where the rider actually rides}';

    protected $description = 'Backfill country_code (complaints, lost items, driver saved places) and office_id (complaints) from the per-shard rows they reference.';

    public function __construct(private CountryNameResolver $countries)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $complaints = Complaint::query()
            ->whereNotNull('booking_id')
            ->where(fn ($q) => $q->whereNull('country_code')->orWhereNull('office_id'))
            ->get(['id', 'booking_id', 'country_code', 'office_id']);

        $lostItems = Schema::connection('global')->hasTable('lost_items')
            ? LostItem::query()->whereNotNull('booking_id')->whereNull('country_code')->get(['id', 'booking_id', 'country_code'])
            : collect();

        if ($complaints->isEmpty() && $lostItems->isEmpty()) {
            // The booking-based pass has nothing to do — but driver places and
            // trip-less complaints are separate populations that may be waiting.
            $places = $this->backfillDriverPlaces($dry);

            if ($places > 0) {
                $this->line(sprintf('%sdriver saved places stamped: %d', $dry ? '[dry-run] ' : '', $places));
            }

            $inferred = $this->option('infer-from-rider') ? $this->inferFromRiders($dry) : 0;

            $this->info($places + $inferred > 0
                ? sprintf('%sdone (places: %d, trip-less complaints: %d)', $dry ? '[dry-run] ' : '', $places, $inferred)
                : 'Nothing to backfill.');

            return self::SUCCESS;
        }

        $bookingIds = $complaints->pluck('booking_id')
            ->merge($lostItems->pluck('booking_id'))
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        // booking id → [country => ['office' => id, 'office_country' => string]].
        // Two countries can share ONE database (Saudi Arabia and Qatar both live
        // in `fleet`), in which case every booking is "found" under both — the
        // office's own country column is what tells them apart.
        $found = [];

        ShardRunner::eachCountry(function ($node) use ($bookingIds, &$found) {
            $country = strtolower((string) $node->country_code);

            $bookings = RideBooking::query()->whereIn('id', $bookingIds)->get(['id', 'office_id']);

            $officeCountries = Office::on(TenantConnection::current())
                ->whereIn('id', $bookings->pluck('office_id')->filter()->unique()->all())
                ->pluck('country', 'id');

            $bookings->each(function ($booking) use (&$found, $country, $officeCountries) {
                $officeId = $booking->office_id !== null ? (int) $booking->office_id : null;

                $found[(int) $booking->id][$country] = [
                    'office' => $officeId,
                    'office_country' => $officeId !== null ? (string) ($officeCountries[$officeId] ?? '') : '',
                ];
            });
        });

        $stats = ['complaints' => 0, 'lost_items' => 0, 'by_office' => 0, 'ambiguous' => 0, 'unmatched' => 0];

        foreach ($complaints as $row) {
            $match = $this->resolve($found, (int) $row->booking_id, $stats);

            if ($match === null) {
                continue;
            }

            [$country, $officeId] = $match;

            $row->country_code = $row->country_code ?? $country;
            $row->office_id = $row->office_id ?? $officeId;

            if (! $dry) {
                $row->save();
            }

            $stats['complaints']++;
        }

        foreach ($lostItems as $row) {
            $match = $this->resolve($found, (int) $row->booking_id, $stats);

            if ($match === null) {
                continue;
            }

            $row->country_code = $match[0];

            if (! $dry) {
                $row->save();
            }

            $stats['lost_items']++;
        }

        $places = $this->backfillDriverPlaces($dry);

        if ($places > 0) {
            $this->line(sprintf('%sdriver saved places stamped: %d', $dry ? '[dry-run] ' : '', $places));
        }

        $inferred = $this->option('infer-from-rider') ? $this->inferFromRiders($dry) : 0;

        if ($this->option('infer-from-rider')) {
            $this->line(sprintf('%strip-less complaints attributed from rider history: %d', $dry ? '[dry-run] ' : '', $inferred));
        }

        $this->info(sprintf(
            '%scomplaints: %d, lost items: %d (%d resolved by office country), ambiguous: %d, unmatched: %d',
            $dry ? '[dry-run] ' : '',
            $stats['complaints'],
            $stats['lost_items'],
            $stats['by_office'],
            $stats['ambiguous'],
            $stats['unmatched']
        ));

        return self::SUCCESS;
    }

    /**
     * Driver saved places sit on the GLOBAL table while drivers live per country
     * and their ids repeat, so an unstamped row is readable by the same-numbered
     * driver in another country. Each row is stamped with the country whose
     * database actually holds that driver — and when two countries share one
     * database, with the country the driver's own record names.
     */
    private function backfillDriverPlaces(bool $dry): int
    {
        try {
            $places = SavedPlace::query()->whereNull('country_code')->whereNotNull('driver_id')->get(['id', 'driver_id', 'country_code']);
        } catch (Throwable $e) {
            return 0;
        }

        if ($places->isEmpty()) {
            return 0;
        }

        $driverIds = $places->pluck('driver_id')->unique()->map(fn ($id) => (int) $id)->all();

        // driver id => [country => the country string on the driver record]
        $found = [];

        ShardRunner::eachCountry(function ($node) use ($driverIds, &$found) {
            $country = strtolower((string) $node->country_code);

            Driver::on(TenantConnection::current())
                ->whereIn('id', $driverIds)
                ->get(['id', 'country'])
                ->each(function ($driver) use (&$found, $country) {
                    $found[(int) $driver->id][$country] = (string) ($driver->country ?? '');
                });
        });

        $stamped = 0;

        foreach ($places as $place) {
            $candidates = $found[(int) $place->driver_id] ?? [];

            if ($candidates === []) {
                continue;
            }

            $country = count($candidates) === 1
                ? array_key_first($candidates)
                : $this->countries->match(reset($candidates), array_keys($candidates));

            if ($country === null) {
                continue;
            }

            $place->country_code = $country;

            if (! $dry) {
                $place->save();
            }

            $stamped++;
        }

        return $stamped;
    }

    /**
     * A complaint filed without a trip has no country of its own. The only
     * honest signal left is where its author actually rides: when ALL of a
     * rider's bookings sit in one database, the complaint belongs to that
     * country too. Riders with history in several databases are left alone.
     */
    private function inferFromRiders(bool $dry): int
    {
        $complaints = Complaint::query()
            ->whereNull('country_code')
            ->whereNull('booking_id')
            ->whereNotNull('user_id')
            ->get(['id', 'user_id', 'country_code']);

        if ($complaints->isEmpty()) {
            return 0;
        }

        $userIds = $complaints->pluck('user_id')->unique()->map(fn ($id) => (int) $id)->all();

        // user id → database → ['countries' => [...], 'office_country' => string]
        $history = [];

        ShardRunner::eachCountry(function ($node) use ($userIds, &$history) {
            $database = strtolower((string) ($node->db_name ?: $node->country_code));
            $country = strtolower((string) $node->country_code);

            $latest = RideBooking::query()
                ->whereIn('user_id', $userIds)
                ->orderByDesc('id')
                ->get(['id', 'user_id', 'office_id'])
                ->groupBy('user_id');

            foreach ($latest as $userId => $bookings) {
                $officeId = (int) ($bookings->first()->office_id ?? 0);

                $history[(int) $userId][$database]['countries'][] = $country;
                $history[(int) $userId][$database]['office_country'] = $officeId > 0
                    ? (string) (Office::on(TenantConnection::current())->whereKey($officeId)->value('country') ?? '')
                    : ($history[(int) $userId][$database]['office_country'] ?? '');
            }
        });

        $attributed = 0;

        foreach ($complaints as $complaint) {
            $databases = $history[(int) $complaint->user_id] ?? [];

            if (count($databases) !== 1) {
                continue;
            }

            $entry = reset($databases);
            $countries = array_values(array_unique($entry['countries']));

            $country = count($countries) === 1
                ? $countries[0]
                : $this->countries->match($entry['office_country'] ?? null, $countries);

            if ($country === null) {
                continue;
            }

            $complaint->country_code = $country;

            if (! $dry) {
                $complaint->save();
            }

            $attributed++;
        }

        return $attributed;
    }

    private function resolve(array $found, int $bookingId, array &$stats): ?array
    {
        $shards = $found[$bookingId] ?? [];

        if ($shards === []) {
            $stats['unmatched']++;

            return null;
        }

        if (count($shards) === 1) {
            $country = array_key_first($shards);

            return [$country, $shards[$country]['office']];
        }

        // Same database, several countries: the office the ride belonged to
        // names its own country, so ask it instead of guessing.
        $country = $this->countries->match(reset($shards)['office_country'] ?? null, array_keys($shards));

        if ($country === null) {
            $stats['ambiguous']++;

            return null;
        }

        $stats['by_office']++;

        return [$country, $shards[$country]['office']];
    }

}
