<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Marketplace\FavoriteOfficeService;
use App\Http\Core\Classes\Places\SavedPlaceService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\ServiceCatalog;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\SiteSetting;
use Throwable;

class HomeService
{
    public function __construct(
        private SavedPlaceService $savedPlaces,
        private FavoriteOfficeService $favorites,
        private OfficeReadModel $offices,
        private FleetWalletService $wallet,
        private RideBookingService $bookings
    ) {
    }

    public function home(int $userId): array
    {
        $currency = strtoupper((string) ShardManager::currency());

        return [
            'services' => $this->services(),
            'saved_places' => $this->savedPlaces->list($userId),
            'favorite_offices' => $this->favoriteOffices($userId),
            'wallet' => [
                'currency_code' => $currency,
                'balance_minor' => $this->wallet->walletBalanceMinor(OwnerType::USER, $userId, $currency),
            ],
            'active_trip' => $this->bookings->activeFor($userId),
            'promo' => $this->promo(),
        ];
    }

    private function services(): array
    {
        $services = [];

        foreach ([ServiceCatalog::RIDE, ServiceCatalog::PREMIUM, ServiceCatalog::TRAVEL] as $service) {
            $services[] = [
                'key' => $service,
                'pricing_style' => ServiceCatalog::style($service),
                'title_key' => 'service.' . $service,
            ];
        }

        return $services;
    }

    private function favoriteOffices(int $userId): array
    {
        $offices = [];

        foreach ($this->favorites->list($userId) as $officeId) {
            $summary = $this->offices->summary((int) $officeId);

            $offices[] = [
                'office_id' => $summary['office_id'],
                'name' => $summary['name'],
                'logo_url' => $summary['logo_url'],
                'rating' => $summary['rating'],
            ];
        }

        return $offices;
    }

    private function promo(): ?array
    {
        try {
            $code = SiteSetting::val('promo_code');

            if (! $code) {
                return null;
            }

            return [
                'code' => $code,
                'title_key' => SiteSetting::val('promo_title'),
                'discount_label' => SiteSetting::val('promo_discount_label'),
            ];
        } catch (Throwable $e) {
            return null;
        }
    }
}
