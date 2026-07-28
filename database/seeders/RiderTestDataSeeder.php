<?php

namespace Database\Seeders;

use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\CorporateInvoice;
use App\Models\DriverPresence;
use App\Models\FamilyMember;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\RiderPaymentMethod;
use App\Models\RiderProfile;
use App\Models\RiderSupportMessage;
use App\Models\RiderSupportTicket;
use App\Models\SafetyContact;
use App\Models\SavedPlace;
use App\Models\Service;
use App\Models\ServiceTariff;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RiderTestDataSeeder extends Seeder
{
    private string $currency = 'SAR';
    private float $pickupLat = 25.2854;
    private float $pickupLng = 51.5310;

    public function run(): void
    {
        $offices = $this->enrichOffices();
        $subs = SubService::query()->get();

        $this->seedTariffsAndPrices($offices, $subs);
        $user = $this->seedRider();
        $this->seedWallet($user);
        $this->seedPlacesAndSafety($user);
        $this->seedPaymentMethod($user);
        $this->seedDriverPresence();
        $this->seedHistory($user, $offices, $subs);
        $this->seedNotifications($user);
        $this->seedSupport($user, $offices);
        $this->seedB2B($user);
        $this->seedScheduled($user, $offices, $subs);

        $this->command?->info("Rider test data ready. Test rider: {$user->phoneNumber} (id {$user->id}), wallet 500.00 {$this->currency}.");
    }

    private function enrichOffices()
    {
        $offices = Office::query()->orderBy('id')->get();
        $palettes = ['a', 'b', 'c', 'd', 'e'];

        foreach ($offices->values() as $i => $office) {
            $office->lat = 25.28 + ($i * 0.012);
            $office->lng = 51.52 + ($i * 0.012);
            $office->is_verified = true;
            $office->is_monitored = $i % 2 === 0;
            $office->rating = round(4.2 + ($i * 0.12), 1);
            $office->ratings_count = 40 + ($i * 12);
            $office->on_time_percentage = 90 + $i;
            $office->avg_response_minutes = 3 + $i;
            $office->initials = mb_strtoupper(mb_substr((string) $office->officeName, 0, 2));
            $office->palette = $palettes[$i % 5];
            $office->ratingExcellent = 30 + $i;
            $office->ratingGood = 8;
            $office->ratingAverage = 2;
            $office->ratingPoor = 1;
            $office->working_hours = ['open' => '00:00', 'close' => '23:59', 'days' => 'all'];
            $office->save();
        }

        return $offices;
    }

    private function seedTariffsAndPrices($offices, $subs): void
    {
        $serviceTravel = Service::query()->pluck('travel_service', 'id');

        foreach ($offices as $office) {
            foreach ($subs as $sub) {
                $isTravel = (bool) $sub->is_travel || (bool) ($serviceTravel[$sub->serviceId] ?? false);

                ServiceTariff::query()->updateOrCreate(
                    ['office_id' => $office->id, 'service' => null, 'service_class' => $sub->name_en],
                    [
                        'currency_code' => $this->currency,
                        'pricing_style' => $isTravel ? 'fixed' : 'meter',
                        'base_minor' => 800,
                        'per_km_minor' => 250,
                        'per_minute_minor' => 40,
                        'minimum_minor' => 1500,
                        'fixed_minor' => $isTravel ? 12000 : 0,
                        'is_active' => true,
                    ]
                );

                OfficeSubServicePrice::query()->updateOrCreate(
                    ['office_id' => $office->id, 'sub_service_id' => $sub->id],
                    ['openPrice' => 8, 'kmPrice' => 2.5, 'minutePrice' => 0.4]
                );
            }
        }
    }

    private function seedRider(): User
    {
        $phone = PhoneNumber::normalize('+974' . '55500500') ?? '+97455500500';

        $user = User::query()->updateOrCreate(
            ['phoneNumber' => $phone],
            [
                'firstName' => 'Test',
                'lastName' => 'Rider',
                'dialCode' => '+974',
                'gender' => 'male',
                'password' => Hash::make('password'),
                'isActive' => 1,
                'is_registered' => true,
                'current_country' => 'QA',
                'referralCode' => 'RIDE500',
            ]
        );

        RiderProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => 'test.rider@fleet.qa',
                'locale' => 'en',
                'notification_prefs' => ['tripUpdates' => true, 'promotions' => true, 'officeMessages' => true, 'safetyAlerts' => true],
                'privacy_prefs' => ['locationDuringTrips' => true, 'shareTripDataWithOffice' => true, 'marketing' => false],
                'auto_share_safety' => true,
            ]
        );

        return $user;
    }

    private function seedWallet(User $user): void
    {
        (new FleetWalletService(new LedgerService()))->topUp(
            (int) $user->id,
            50000,
            $this->currency,
            'seed:wallet:' . $this->currency . ':' . $user->id,
            'seed',
            (int) $user->id
        );
    }

    private function seedPlacesAndSafety(User $user): void
    {
        SavedPlace::query()->updateOrCreate(
            ['user_id' => $user->id, 'label' => 'Home'],
            ['icon' => 'home', 'title' => 'Home', 'address' => 'West Bay, Doha', 'lat' => 25.3213, 'lng' => 51.5310]
        );
        SavedPlace::query()->updateOrCreate(
            ['user_id' => $user->id, 'label' => 'Work'],
            ['icon' => 'work', 'title' => 'Work', 'address' => 'Msheireb, Doha', 'lat' => 25.2867, 'lng' => 51.5333]
        );

        SafetyContact::query()->updateOrCreate(
            ['user_id' => $user->id, 'phone' => '+97455511111'],
            ['name' => 'Family Member', 'relation' => 'spouse', 'is_primary' => true, 'auto_share' => true]
        );
    }

    private function seedPaymentMethod(User $user): void
    {
        RiderPaymentMethod::query()->updateOrCreate(
            ['user_id' => $user->id, 'stripe_payment_method_id' => 'pm_seed_visa'],
            ['type' => 'card', 'brand' => 'visa', 'last4' => '4242', 'exp' => '12/28', 'gateway_token' => 'pm_seed_visa', 'is_default' => true]
        );
    }

    private function seedDriverPresence(): void
    {
        $drivers = DB::table('drivers')->where('status', 'active')->orderBy('id')->limit(10)->get();

        foreach ($drivers->values() as $j => $driver) {
            DriverPresence::query()->updateOrCreate(
                ['driver_id' => (int) $driver->id],
                [
                    'office_id' => (int) $driver->officeId,
                    'status' => PresenceStatus::ONLINE,
                    'lat' => $this->pickupLat + (($j - 5) * 0.0018),
                    'lng' => $this->pickupLng + (($j - 5) * 0.0018),
                    'heartbeat_at' => now()->addHours(12),
                ]
            );
        }
    }

    private function seedHistory(User $user, $offices, $subs): void
    {
        if (RideBooking::query()->where('user_id', $user->id)->where('source', 'seed')->exists()) {
            return;
        }

        $office = $offices->first();
        $sub = $subs->first();
        $driver = DB::table('drivers')->where('officeId', $office->id)->value('id') ?? 2;

        foreach ([1, 2, 3] as $n) {
            $booking = RideBooking::query()->create([
                'user_id' => $user->id,
                'office_id' => $office->id,
                'driver_id' => (int) $driver,
                'source' => 'seed',
                'service' => 'ride',
                'service_class' => $sub->name_en,
                'pricing_style' => 'meter',
                'status' => 'completed',
                'pickup_lat' => $this->pickupLat,
                'pickup_lng' => $this->pickupLng,
                'pickup_title' => 'West Bay, Doha',
                'dropoff_lat' => 25.2731,
                'dropoff_lng' => 51.6080,
                'dropoff_title' => 'Hamad Intl. Airport',
                'distance_m' => 14800,
                'duration_s' => 1320,
                'currency_code' => $this->currency,
                'fare_minor' => 2400 + ($n * 300),
                'total_minor' => 2400 + ($n * 300),
                'payment_method' => 'wallet',
                'assigned_at' => now()->subDays($n)->addMinutes(2),
                'completed_at' => now()->subDays($n)->addMinutes(30),
                'rated_at' => now()->subDays($n)->addMinutes(31),
            ]);

            RideRating::query()->create([
                'booking_id' => $booking->id, 'rater_type' => 'user', 'rater_id' => $user->id,
                'ratee_type' => 'driver', 'ratee_id' => (int) $driver, 'stars' => 5,
                'tags' => ['clean', 'polite'], 'comment' => 'Great ride', 'book_again' => true, 'favorite' => false,
                'created_at' => now()->subDays($n)->addMinutes(31),
            ]);
            RideRating::query()->create([
                'booking_id' => $booking->id, 'rater_type' => 'user', 'rater_id' => $user->id,
                'ratee_type' => 'office', 'ratee_id' => $office->id, 'stars' => 5,
                'comment' => 'Reliable office', 'created_at' => now()->subDays($n)->addMinutes(31),
            ]);
        }
    }

    private function seedSupport(User $user, $offices): void
    {
        if (RiderSupportTicket::query()->where('user_id', $user->id)->exists()) {
            return;
        }

        $ticket = RiderSupportTicket::query()->create([
            'user_id' => $user->id,
            'office_id' => $offices->first()->id,
            'category' => 'payment',
            'topic' => 'payment',
            'layer' => 'fleetos',
            'subject' => 'Overcharged on last trip',
            'status' => 'open',
            'last_message_at' => now()->subHours(2),
        ]);

        RiderSupportMessage::query()->create(['ticket_id' => $ticket->id, 'sender_type' => 'user', 'sender_id' => $user->id, 'body' => 'I think I was charged twice for my airport trip.', 'created_at' => now()->subHours(3)]);
        RiderSupportMessage::query()->create(['ticket_id' => $ticket->id, 'sender_type' => 'agent', 'sender_id' => 1, 'body' => 'Thanks for reaching out — we are reviewing your trip now.', 'created_at' => now()->subHours(2)]);

        Complaint::query()->create([
            'user_id' => $user->id,
            'about' => 'driver',
            'description' => 'Driver took a longer route than needed.',
            'routed_to' => 'office',
            'priority' => 'normal',
            'case_ref' => 'C-SEED0001',
            'status' => 'open',
        ]);
    }

    private function seedB2B(User $user): void
    {
        if (FamilyMember::query()->where('user_id', $user->id)->exists()) {
            return;
        }

        FamilyMember::query()->create(['user_id' => $user->id, 'name' => 'Sara Rider', 'phone' => '+97455522222', 'type' => 'minor', 'approval_required' => true, 'auto_share' => true]);
        FamilyMember::query()->create(['user_id' => $user->id, 'name' => 'Omar Rider', 'phone' => '+97455533333', 'type' => 'adult', 'approval_required' => false, 'auto_share' => false]);

        CorporateInvoice::query()->create(['user_id' => $user->id, 'month' => '2026-06', 'trips' => 14, 'amount_minor' => 52000, 'currency_code' => $this->currency, 'status' => 'billed']);
        CorporateInvoice::query()->create(['user_id' => $user->id, 'month' => '2026-07', 'trips' => 9, 'amount_minor' => 33500, 'currency_code' => $this->currency, 'status' => 'unbilled']);
    }

    private function seedScheduled(User $user, $offices, $subs): void
    {
        if (RideBooking::query()->where('user_id', $user->id)->where('status', 'scheduled')->exists()) {
            return;
        }

        RideBooking::query()->create([
            'user_id' => $user->id,
            'office_id' => $offices->first()->id,
            'source' => 'seed',
            'service' => 'travel',
            'service_class' => $subs->firstWhere('is_travel', true)->name_en ?? $subs->first()->name_en,
            'pricing_style' => 'fixed',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDays(2)->setTime(9, 30),
            'passengers' => 2,
            'luggage' => 3,
            'flight_no' => 'QR1234',
            'pickup_lat' => 25.3213, 'pickup_lng' => 51.5310, 'pickup_title' => 'West Bay, Doha',
            'dropoff_lat' => 25.2731, 'dropoff_lng' => 51.6080, 'dropoff_title' => 'Hamad Intl. Airport',
            'distance_m' => 16200, 'duration_s' => 1500,
            'currency_code' => $this->currency, 'fare_minor' => 12000, 'total_minor' => 12000, 'payment_method' => 'wallet',
        ]);
    }

    private function seedNotifications(User $user): void
    {
        if (AppNotification::query()->where('notifiable_type', 'user')->where('notifiable_id', $user->id)->exists()) {
            return;
        }

        $rows = [
            ['ride_completed', 'trip', 'Trip completed', 'Your fare was 24.00 QAR', ['tripId' => 1], now()->subDays(1)],
            ['promo_offer', 'promo', 'Weekend offer', 'Use QATAR10 for 10% off', ['code' => 'QATAR10'], null],
            ['wallet_credited', 'wallet', 'Wallet topped up', '500.00 QAR added to your wallet', ['amount' => 50000], null],
        ];

        foreach ($rows as [$key, $type, $title, $body, $data, $readAt]) {
            AppNotification::query()->create([
                'notifiable_type' => 'user', 'notifiable_id' => $user->id,
                'template_key' => $key, 'type' => $type, 'locale' => 'en',
                'title' => $title, 'body' => $body, 'data' => $data, 'read_at' => $readAt,
            ]);
        }
    }
}
