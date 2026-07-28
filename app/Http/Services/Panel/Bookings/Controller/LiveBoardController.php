<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class LiveBoardController extends Controller
{
    public function __invoke(
        EntityScope $scope,
        OfficeRepository $offices
    ): View {
        $isAdmin = $scope->isAdmin();

        // Shard-scope the realtime channel so the board only receives THIS
        // country's order events — matches the prefix OrderRedisModel broadcasts
        // under (keeps countries isolated on the socket, not just in the DB).
        $shardKey = ShardManager::shardKey();
        $prefix   = $shardKey !== '' ? $shardKey . ':' : '';
        $channel  = $prefix . ($isAdmin ? 'panel-orders-admins' : 'panel-orders-office-' . $scope->officeId());
        $user    = $scope->user();

        return view('panel.bookings.live', [
            'entity'          => $scope->guard(),
            'isAdmin'         => $isAdmin,
            'canEdit'         => $user && $user->can(PanelPermission::EDIT_ORDER_STATUS),
            'officeOptions'   => $isAdmin ? $offices->options() : [],
            'realtimeUrl'     => config('services.realtime.url'),
            'realtimeChannel' => $channel,
            'realtimeEnabled' => (bool) config('services.realtime.order_board'),
            // App-pipeline rides (isRide) open in the new RideBooking detail page,
            // not the legacy Booking one their numeric ids do not exist in.
            'showRideBase'    => route("panel.{$scope->guard()}.rides.show", ['ride' => '__ID__']),
        ]);
    }
}
