<?php

namespace App\Http\Services\Panel\Home\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Home\Logic\DashboardData;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Wallet\WalletReveal;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        $data = new DashboardData($scope);

        $revealed = WalletReveal::isRevealed();
        $balances = $data->currencyBalances();

        if (! $revealed) {
            $balances = array_map(
                fn (array $b) => ['code' => $b['code'], 'symbol' => $b['symbol'], 'balance' => null],
                $balances
            );
        }

        $node    = ShardContext::current();
        $channel = $scope->isAdmin() ? 'admins' : ('office' . $scope->officeId());
        $user    = $scope->user();

        return view('panel.home.index', [
            'entity'              => $scope->guard(),
            'user'                => $user,
            'userName'            => $this->displayName($user),
            'countryName'         => $node?->name,
            'isAdmin'             => $scope->isAdmin(),
            'counters'            => $data->counters(),
            'liveKpis'            => $data->liveKpis(),
            'heroStats'           => $data->heroStats(),
            'periodStats'         => $data->periodStats(),
            'wallet'              => $data->walletSummary(),
            'currencyBalances'    => $balances,
            'walletRevealed'      => $revealed,
            'walletRevealSeconds' => $revealed ? WalletReveal::secondsLeft() : 0,
            'monthlyRevenue'      => $data->monthlyRevenue(),
            'periodStatus'        => $data->periodStatus(),
            'recentOrders'        => $data->recentOrders(),
            'rankings'            => $data->rankings(),
            'rides'               => $scope->isAdmin() ? $data->rides() : null,
            'googleMapsKey'       => config('services.google_maps.key'),
            'realtimeUrl'         => config('services.realtime.url'),
            'realtimeChannel'     => $channel,
            'realtimeEvent'       => $channel . ':' . ($scope->isAdmin() ? 'admin-satistic' : 'office-satistic'),
            'mapCenter'           => [
                'lat' => $node && $node->lat ? (float) $node->lat : 24.7136,
                'lng' => $node && $node->lng ? (float) $node->lng : 46.6753,
            ],
        ]);
    }

    private function displayName(?object $user): string
    {
        if (! $user) {
            return '';
        }

        $name = $user->displayName
            ?? $user->officeName
            ?? trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));

        return trim((string) $name);
    }
}
