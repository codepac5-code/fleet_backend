<?php

namespace App\Http\Core\Const\Ride;

class BookingStatus
{
    const SCHEDULED = 'scheduled';
    const MATCHING = 'matching';
    const ASSIGNED = 'assigned';
    const ARRIVING = 'arriving';
    const ARRIVED = 'arrived';
    const ON_TRIP = 'on_trip';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
    const REJECTED = 'rejected';

    // Office-mediated fixed-trip states. The rider picks an office offer, the
    // office accepts or declines, then a driver is assigned. These live ONLY on
    // the fixed flow; the meter pipeline never enters them.
    //   pending_acceptance : rider chose an office, fare locked + held, waiting
    //                        for the office to accept.
    //   confirmed          : office accepted; fare stays held, waiting for a
    //                        driver (the spec's "driver_pending" is this state
    //                        with driver_id IS NULL).
    //   declined           : office declined and no backup office could be
    //                        offered at the same-or-better fare (terminal).
    //   no_driver_expired  : no driver was assigned by the SLA (terminal).
    const PENDING_ACCEPTANCE = 'pending_acceptance';
    const CONFIRMED = 'confirmed';
    const DECLINED = 'declined';
    const NO_DRIVER_EXPIRED = 'no_driver_expired';

    const LIVE_SUB = [self::ARRIVING, self::ARRIVED, self::ON_TRIP];

    // Every state a fixed trip rests in before a driver is assigned — gates
    // edits, cancellation policy, and the office-acceptance transitions.
    const FIXED_PRE_DRIVER = [self::PENDING_ACCEPTANCE, self::CONFIRMED];

    const TERMINAL = [
        self::COMPLETED,
        self::CANCELLED,
        self::REJECTED,
        self::DECLINED,
        self::NO_DRIVER_EXPIRED,
    ];
}
