<?php

namespace App\Http\Core\Const\Event;

class EventType
{
    const DISPATCH_OFFER_CREATED = 'dispatch.offer_created';
    const DISPATCH_RIDE_ASSIGNED = 'dispatch.ride_assigned';
    const DISPATCH_OFFER_EXPIRED = 'dispatch.offer_expired';
    const DISPATCH_JOB_CANCELLED = 'dispatch.job_cancelled';
    const BOOKING_STATUS_CHANGED = 'booking.status_changed';
    const ORDER_CREATED = 'order.created';
    const BOOKING_METER = 'booking.meter';
    const DRIVER_LOCATION = 'driver.location';
    const BOOKING_CHAT_MESSAGE = 'booking.chat_message';
    const PRESENCE_CHANGED = 'presence.changed';
    const WALLET_CREDITED = 'wallet.credited';
    const WALLET_PAYOUT = 'wallet.payout';
    const PAYMENT_SUCCEEDED = 'payment.succeeded';
    const RIDE_RELEASED = 'ride.released';
    const CHAT_MESSAGE_CREATED = 'chat.message_created';
    const RATING_RECEIVED = 'rating.received';
    const SUPPORT_MESSAGE_CREATED = 'support.message_created';
    const NOTIFICATION_CREATED = 'notification.created';
    const SUBSCRIPTION_ACTIVATED = 'subscription.activated';
    const SUBSCRIPTION_PAST_DUE = 'subscription.past_due';
    const SUBSCRIPTION_CANCELED = 'subscription.canceled';
    const OVERAGE_INVOICED = 'overage.invoiced';
}
