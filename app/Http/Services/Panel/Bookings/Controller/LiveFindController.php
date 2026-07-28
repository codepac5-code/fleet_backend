<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Http\Services\Panel\Bookings\Logic\BookingRepository;
use App\Http\Services\Panel\Bookings\Logic\LiveTripPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveFindController extends Controller
{
    public function __invoke(Request $request, BookingRepository $bookings): JsonResponse
    {
        $id = (int) $request->query('id');

        if ($id <= 0) {
            return response()->json(['found' => false]);
        }

        try {
            $order = $bookings->findOrFail($id);
        } catch (\Throwable $e) {
            return response()->json(['found' => false]);
        }

        if (! empty($order->is_scheduled)) {
            return response()->json(['found' => false]);
        }

        if (LiveTripPresenter::groupOf($order->status) === 'cancelled') {
            return response()->json(['found' => false]);
        }

        $cached = OrderRedisModel::getOrder($id);

        if (! $cached || ! empty($cached->is_scheduled)) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'trip'  => LiveTripPresenter::fromOrder($cached),
        ]);
    }
}
