<?php

namespace App\Http\Core\Const\Support;

/**
 * The GOVERNED support-ticket lifecycle. A ticket may only move along the legal
 * edges below, and only an actor with the right role may drive a given edge —
 * so a rider can't resolve their own ticket, a closed ticket can't silently
 * reopen, and staff can't skip the machine. Mirrors the lost-found governance.
 *
 *   OPEN      → needs staff attention (new, rider replied, or reopened)
 *   PENDING   → staff is handling it / awaiting the rider
 *   RESOLVED  → staff marked it solved; a rider reply reopens it
 *   CLOSED    → terminal; a new problem needs a new ticket
 */
class SupportStatus
{
    const OPEN = 'open';
    const PENDING = 'pending';
    const RESOLVED = 'resolved';
    const CLOSED = 'closed';

    const ALL = [self::OPEN, self::PENDING, self::RESOLVED, self::CLOSED];

    /** No transition may leave these. */
    const TERMINAL = [self::CLOSED];

    /** Legal edges, regardless of who drives them: from => [allowed next]. */
    const TRANSITIONS = [
        self::OPEN => [self::PENDING, self::RESOLVED, self::CLOSED],
        self::PENDING => [self::OPEN, self::RESOLVED, self::CLOSED],
        self::RESOLVED => [self::OPEN, self::CLOSED],
        self::CLOSED => [],
    ];

    /**
     * Which target statuses each ACTOR ROLE may set (on a legal edge). Staff
     * (office/fleetos) run the workflow; a rider may only close their own ticket
     * (reopening happens implicitly by replying, not via setStatus).
     */
    const ROLE_TARGETS = [
        SupportActor::RIDER => [self::CLOSED],
        SupportActor::OFFICE => [self::OPEN, self::PENDING, self::RESOLVED, self::CLOSED],
        SupportActor::FLEETOS => [self::OPEN, self::PENDING, self::RESOLVED, self::CLOSED],
    ];

    public static function isValid(string $status): bool
    {
        return in_array($status, self::ALL, true);
    }

    public static function isTerminal(string $status): bool
    {
        return in_array($status, self::TERMINAL, true);
    }

    /** Is `from → to` a legal edge of the machine? */
    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /** May this actor role set `to` — both a legal edge AND within the role's authority? */
    public static function roleCanTransition(string $role, string $from, string $to): bool
    {
        return self::canTransition($from, $to)
            && in_array($to, self::ROLE_TARGETS[$role] ?? [], true);
    }
}
