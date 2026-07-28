<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\RideBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefundBookingController extends Controller
{
    public function __invoke(int $booking, Request $request, EntityScope $scope, FleetWalletService $wallet, AuditLogService $audit, ?EventBus $events = null): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // RideBooking is per-country → this refund can only ever touch a booking
        // on the active shard; offices are further limited to their own bookings.
        $model = RideBooking::query()->find($booking);

        if ($model === null || (! $scope->isAdmin() && (int) $model->office_id !== (int) $scope->officeId())) {
            return back()->with('error', textByLanguage('غير مسموح', 'Not allowed'));
        }

        $amountMinor = (int) round(((float) $data['amount']) * 100);
        $total = (int) ($model->total_minor ?? 0);

        if ($amountMinor <= 0 || $amountMinor > $total) {
            return back()->with('error', textByLanguage('مبلغ غير صالح', 'Invalid amount'));
        }

        // One manual refund per booking — the deterministic key makes a double
        // submit a no-op at the ledger, so no accidental double-refund.
        $key = 'manual_refund:' . $model->id;

        $wallet->refundFromFleet((int) $model->id, (int) $model->user_id, $amountMinor, (string) $model->currency_code, $key);

        if ($events !== null) {
            $events->emit(new DomainEvent(
                EventType::WALLET_CREDITED,
                [Channel::user((int) $model->user_id)],
                ['amount' => $amountMinor, 'currency' => (string) $model->currency_code, 'booking_id' => (int) $model->id, 'reason' => 'refund'],
            ));
        }

        $audit->record(
            'booking.refunded',
            $scope->isAdmin() ? 'admin' : 'office',
            $scope->isAdmin() ? null : $scope->officeId(),
            'booking',
            (int) $model->id,
            array_filter(['amount_minor' => $amountMinor, 'reason' => $data['reason'] ?? null]),
            $request->ip()
        );

        return back()->with('status', textByLanguage('تمّ إصدار الاسترداد', 'Refund issued'));
    }
}
