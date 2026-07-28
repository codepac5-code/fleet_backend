<?php

namespace App\Console\Commands;

use App\Http\Core\GeoServices\ShardRunner;
use App\Models\Complaint;
use App\Models\LostItem;
use App\Models\RideBooking;
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
    protected $signature = 'fleet:support-backfill {--dry-run : report what would change without writing}';

    protected $description = 'Backfill country_code (complaints, lost items) and office_id (complaints) from the per-shard booking each row references.';

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
            $this->info('Nothing to backfill.');

            return self::SUCCESS;
        }

        $bookingIds = $complaints->pluck('booking_id')
            ->merge($lostItems->pluck('booking_id'))
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        // booking id → [country => office_id]; a booking id present in more than
        // one shard is ambiguous and gets skipped.
        $found = [];

        ShardRunner::eachCountry(function ($node) use ($bookingIds, &$found) {
            $country = strtolower((string) $node->country_code);

            RideBooking::query()
                ->whereIn('id', $bookingIds)
                ->get(['id', 'office_id'])
                ->each(function ($booking) use (&$found, $country) {
                    $found[(int) $booking->id][$country] = $booking->office_id !== null ? (int) $booking->office_id : null;
                });
        });

        $stats = ['complaints' => 0, 'lost_items' => 0, 'ambiguous' => 0, 'unmatched' => 0];

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

        $this->info(sprintf(
            '%scomplaints: %d, lost items: %d, ambiguous: %d, unmatched: %d',
            $dry ? '[dry-run] ' : '',
            $stats['complaints'],
            $stats['lost_items'],
            $stats['ambiguous'],
            $stats['unmatched']
        ));

        return self::SUCCESS;
    }

    private function resolve(array $found, int $bookingId, array &$stats): ?array
    {
        $shards = $found[$bookingId] ?? [];

        if ($shards === []) {
            $stats['unmatched']++;

            return null;
        }

        if (count($shards) > 1) {
            $stats['ambiguous']++;

            return null;
        }

        return [array_key_first($shards), reset($shards)];
    }
}
