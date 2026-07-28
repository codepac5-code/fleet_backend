<?php

namespace App\Http\Core\Const\Support;

/**
 * Who is acting on a support ticket. Distinct from {@see SupportLayer} (which
 * routes a ticket to a desk): the actor is the party performing a transition,
 * and governs what they're allowed to do.
 */
class SupportActor
{
    /** The ticket's owner (the customer). */
    const RIDER = 'rider';

    /** An office agent handling the office desk. */
    const OFFICE = 'office';

    /** A FleetOS platform agent handling escalations / the platform desk. */
    const FLEETOS = 'fleetos';

    const ALL = [self::RIDER, self::OFFICE, self::FLEETOS];

    public static function isStaff(string $role): bool
    {
        return $role === self::OFFICE || $role === self::FLEETOS;
    }
}
