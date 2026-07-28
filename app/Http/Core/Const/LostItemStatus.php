<?php

namespace App\Http\Core\Const;

/**
 * Lost & found lifecycle + who may drive each transition.
 *
 *   reported ─▶ acknowledged ─▶ matched ─▶ ready_for_handback ─▶ returned
 *        └────────────┴──────────▶ unresolved            (office closes, not found)
 *        └── cancelled                                   (reporter withdraws)
 *
 * A report is filed by a RIDER (lost) or a DRIVER (found); the OFFICE arbitrates
 * — it acknowledges, confirms a suggested match, arranges the hand-back and
 * closes it returned or unresolved.
 */
class LostItemStatus
{
    // Reporter side.
    const REPORTER_RIDER = 'rider';
    const REPORTER_DRIVER = 'driver';

    // Lifecycle.
    const REPORTED = 'reported';
    const ACKNOWLEDGED = 'acknowledged';
    const MATCHED = 'matched';
    const READY = 'ready_for_handback';
    const RETURNED = 'returned';
    const UNRESOLVED = 'unresolved';
    const CANCELLED = 'cancelled';

    /** Terminal states — no further transition. */
    const TERMINAL = [self::RETURNED, self::UNRESOLVED, self::CANCELLED];

    /** Open (non-terminal) states an office still works. */
    const OPEN = [self::REPORTED, self::ACKNOWLEDGED, self::MATCHED, self::READY];

    /**
     * Allowed office transitions: from => [to, …]. Matching (→ matched) is a
     * separate confirm action; cancelling is the reporter's, not the office's.
     */
    const OFFICE_TRANSITIONS = [
        self::REPORTED => [self::ACKNOWLEDGED, self::UNRESOLVED],
        self::ACKNOWLEDGED => [self::MATCHED, self::UNRESOLVED],
        self::MATCHED => [self::READY, self::UNRESOLVED],
        self::READY => [self::RETURNED, self::UNRESOLVED],
    ];

    /** A reporter may cancel their own report only while it is still early. */
    const CANCELLABLE_FROM = [self::REPORTED, self::ACKNOWLEDGED];

    public static function isTerminal(string $status): bool
    {
        return in_array($status, self::TERMINAL, true);
    }

    public static function canOffice(string $from, string $to): bool
    {
        return in_array($to, self::OFFICE_TRANSITIONS[$from] ?? [], true);
    }
}
