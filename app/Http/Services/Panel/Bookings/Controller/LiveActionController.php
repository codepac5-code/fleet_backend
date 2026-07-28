<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\LiveTripPresenter;
use App\Http\Services\Panel\Bookings\Request\LiveActionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LiveActionController extends Controller
{
    public function __invoke(LiveActionRequest $request, int $booking, BookingRepository $bookings): JsonResponse
    {
        $order  = $bookings->findOrFail($booking);
        $action = $request->validated('action');

        if (! empty($order->is_scheduled)) {
            return response()->json([
                'ok'      => false,
                'message' => textByLanguage('هذا الإجراء للرحلات الفورية فقط', 'This action is for immediate trips only'),
            ], 422);
        }

        $old = $order->status;
        $new = match ($action) {
            'hold'                              => OrderStatus::$Hold,
            'cancel'                            => OrderStatus::$Cancelled,
            'complete_paid', 'complete_unpaid'  => OrderStatus::$Completed,
        };

        if ($action === 'cancel') {
            $order->reason      = $request->validated('reason');
            $order->cancelledAt = now();
        } elseif ($action === 'complete_paid') {
            $order->paymentStatus = 'paid';
        } elseif ($action === 'complete_unpaid') {
            $order->paymentStatus = 'unpaid';
        }

        $order->status = $new;
        $order->save();

        try {
            OrderRedisModel::updateStatus($order, $old, $new);
        } catch (\Throwable $e) {
            Log::warning('Live action redis sync failed: ' . $e->getMessage());
        }

        $cached = OrderRedisModel::getOrder($order->id);
        $trip   = $cached ? LiveTripPresenter::fromOrder($cached) : null;

        $messages = [
            'hold'            => textByLanguage('تم تعليق الرحلة', 'Trip put on hold'),
            'cancel'          => textByLanguage('تم إلغاء الرحلة', 'Trip cancelled'),
            'complete_paid'   => textByLanguage('تم إكمال الرحلة (مدفوعة)', 'Trip completed (paid)'),
            'complete_unpaid' => textByLanguage('تم إكمال الرحلة (غير مدفوعة)', 'Trip completed (unpaid)'),
        ];

        return response()->json([
            'ok'      => true,
            'message' => $messages[$action],
            'trip'    => $trip,
        ]);
    }
}
