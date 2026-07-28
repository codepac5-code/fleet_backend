<?php

namespace App\Http\Core\Classes\Support;

use App\Http\Core\Const\LostItemStatus as St;
use App\Http\Core\Exceptions\DomainException;
use App\Models\LostItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Governance for the lost & found workflow between a RIDER, a DRIVER and the
 * OFFICE that arbitrates the hand-back.
 *
 *   rider reports LOST  ─┐
 *                        ├─▶ office acknowledges ─▶ office CONFIRMS a suggested
 *   driver reports FOUND ─┘     match (lost ↔ found on the same booking) ─▶
 *                              ready_for_handback ─▶ returned
 *                                                 └▶ unresolved
 *
 * Matching is AUTO-SUGGESTED (opposite-type open reports on the same booking)
 * and OFFICE-CONFIRMED — the office verifies before anyone's property is handed
 * over. Every state transition is guarded by [LostItemStatus].
 */
class LostFoundService
{
    /** A rider files a lost-item report against their completed trip. */
    public function reportLost(int $userId, int $bookingId, int $officeId, array $in): LostItem
    {
        return LostItem::query()->create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'reporter_type' => St::REPORTER_RIDER,
            'office_id' => $officeId,
            'ticket_id' => $in['ticket_id'] ?? null,
            'category' => (string) $in['category'],
            'description' => $in['description'] ?? null,
            'photo' => $in['photo'] ?? null,
            'share_masked_number' => (bool) ($in['share_masked_number'] ?? false),
            'status' => St::REPORTED,
        ]);
    }

    /** A driver files a found-item report against a trip they drove. */
    public function reportFound(int $driverId, int $userId, int $bookingId, int $officeId, array $in): LostItem
    {
        return LostItem::query()->create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'reporter_type' => St::REPORTER_DRIVER,
            'driver_id' => $driverId,
            'office_id' => $officeId,
            'category' => (string) $in['category'],
            'description' => $in['description'] ?? null,
            'photo' => $in['photo'] ?? null,
            'share_masked_number' => (bool) ($in['share_masked_number'] ?? true),
            'status' => St::REPORTED,
        ]);
    }

    /**
     * Auto-suggested matches for a report: the OPPOSITE-side open reports on the
     * SAME booking that aren't matched yet. This is only a suggestion — the
     * office confirms the actual pair.
     *
     * @return \Illuminate\Support\Collection<int, LostItem>
     */
    public function suggestedMatches(LostItem $item): \Illuminate\Support\Collection
    {
        $opposite = $item->reporter_type === St::REPORTER_RIDER
            ? St::REPORTER_DRIVER
            : St::REPORTER_RIDER;

        return LostItem::query()
            ->where('booking_id', $item->booking_id)
            ->where('reporter_type', $opposite)
            ->whereNull('matched_item_id')
            ->whereIn('status', St::OPEN)
            ->orderBy('id')
            ->get();
    }

    /**
     * The office confirms a suggested LOST ↔ FOUND pair. Both reports must be for
     * the same booking, opposite sides, open, unmatched, and belong to this
     * office. Links them and moves both to `matched`.
     */
    public function confirmMatch(int $officeId, int $itemId, int $otherId): LostItem
    {
        return DB::transaction(function () use ($officeId, $itemId, $otherId) {
            $a = $this->ownedByOffice($officeId, $itemId);
            $b = $this->ownedByOffice($officeId, $otherId);

            if ($a->booking_id !== $b->booking_id) {
                throw DomainException::make('match_different_booking', 422);
            }
            if ($a->reporter_type === $b->reporter_type) {
                throw DomainException::make('match_same_side', 422);
            }
            foreach ([$a, $b] as $x) {
                if ($x->matched_item_id !== null) {
                    throw DomainException::make('already_matched', 409);
                }
                if (! in_array($x->status, St::OPEN, true)) {
                    throw DomainException::make('not_matchable', 409);
                }
            }

            $now = Carbon::now();
            foreach ([[$a, $b], [$b, $a]] as [$x, $y]) {
                $x->matched_item_id = (int) $y->id;
                $x->status = St::MATCHED;
                $x->matched_at = $now;
                $x->save();
            }

            return $a->fresh();
        });
    }

    /**
     * An office lifecycle transition (acknowledge / ready / returned /
     * unresolved). `returned` and `unresolved` also close the matched pair so
     * both sides never disagree on the outcome.
     */
    public function officeTransition(int $officeId, int $itemId, string $to, ?string $resolution = null): LostItem
    {
        return DB::transaction(function () use ($officeId, $itemId, $to, $resolution) {
            $item = $this->ownedByOffice($officeId, $itemId);

            if (! St::canOffice((string) $item->status, $to)) {
                throw DomainException::make('invalid_transition', 409);
            }

            $this->applyStatus($item, $to, $resolution);

            // Keep the paired report in lockstep on a terminal outcome.
            if (in_array($to, [St::RETURNED, St::UNRESOLVED], true) && $item->matched_item_id !== null) {
                $mate = LostItem::query()->find((int) $item->matched_item_id);
                if ($mate !== null && ! St::isTerminal((string) $mate->status)) {
                    $this->applyStatus($mate, $to, $resolution);
                }
            }

            return $item->fresh();
        });
    }

    /** A reporter withdraws their own report while it is still early. */
    public function cancel(string $reporterType, int $reporterId, int $itemId): LostItem
    {
        $item = LostItem::query()->find($itemId);
        if ($item === null) {
            throw DomainException::notFound('lost_item_not_found');
        }

        $owns = $reporterType === St::REPORTER_RIDER
            ? ((int) $item->user_id === $reporterId && $item->reporter_type === St::REPORTER_RIDER)
            : ((int) $item->driver_id === $reporterId && $item->reporter_type === St::REPORTER_DRIVER);

        if (! $owns) {
            throw DomainException::notFound('lost_item_not_found');
        }
        // Only REPORTED / ACKNOWLEDGED reports can be withdrawn — once the office
        // has confirmed a match the office owns the outcome, so a report can no
        // longer be matched (matched_item_id is only ever set together with the
        // non-cancellable `matched` status).
        if (! in_array($item->status, St::CANCELLABLE_FROM, true)) {
            throw DomainException::make('not_cancellable', 409);
        }

        $item->status = St::CANCELLED;
        $item->save();

        return $item;
    }

    /** A rider's own LOST reports, newest first (for their app). */
    public function forRider(int $userId): array
    {
        return LostItem::query()
            ->where('user_id', $userId)
            ->where('reporter_type', St::REPORTER_RIDER)
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (LostItem $i) => self::present($i))
            ->all();
    }

    /** A driver's own FOUND reports, newest first (for their app). */
    public function forDriver(int $driverId): array
    {
        return LostItem::query()
            ->where('driver_id', $driverId)
            ->where('reporter_type', St::REPORTER_DRIVER)
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (LostItem $i) => self::present($i))
            ->all();
    }

    /** App-facing view of a report: the governed status + its audit stamps. */
    public static function present(LostItem $item): array
    {
        return [
            'id' => (int) $item->id,
            'booking_id' => (int) $item->booking_id,
            'reporter_type' => (string) $item->reporter_type,
            'category' => (string) $item->category,
            'description' => $item->description,
            'photo' => $item->photo,
            'status' => (string) $item->status,
            'is_matched' => $item->matched_item_id !== null,
            'is_terminal' => St::isTerminal((string) $item->status),
            'cancellable' => in_array($item->status, St::CANCELLABLE_FROM, true),
            'resolution' => $item->resolution,
            'matched_at' => optional($item->matched_at)->toIso8601ZuluString(),
            'returned_at' => optional($item->returned_at)->toIso8601ZuluString(),
            'created_at' => optional($item->created_at)->toIso8601ZuluString(),
        ];
    }

    // ── internals ────────────────────────────────────────────────────

    private function applyStatus(LostItem $item, string $to, ?string $resolution): void
    {
        $item->status = $to;
        if ($to === St::RETURNED) {
            $item->returned_at = Carbon::now();
        }
        if (in_array($to, [St::RETURNED, St::UNRESOLVED], true) && $resolution !== null) {
            $item->resolution = $resolution;
        }
        $item->save();
    }

    private function ownedByOffice(int $officeId, int $itemId): LostItem
    {
        $item = LostItem::query()->find($itemId);
        if ($item === null || (int) $item->office_id !== $officeId) {
            throw DomainException::notFound('lost_item_not_found');
        }

        return $item;
    }
}
