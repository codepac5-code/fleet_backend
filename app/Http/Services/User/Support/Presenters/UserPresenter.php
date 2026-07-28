<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\RiderProfile;
use App\Models\RiderSupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserPresenter
{
    public function present(User $user): array
    {
        $profile = RiderProfile::query()->where('user_id', $user->id)->first();

        $openTickets = RiderSupportTicket::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->count();

        $favoriteOffices = DB::table('favorite_offices')->where('user_id', $user->id)->count();

        return [
            'id' => (int) $user->id,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'phoneNumber' => $user->phoneNumber,
            'dialCode' => $user->dialCode,
            'gender' => $user->gender,
            'photo' => $this->photoUrl($user->photo),
            'isActive' => (bool) $user->isActive,
            'referralCode' => $user->referralCode,
            'walletBalance' => (float) ($user->walletBalance ?? 0),
            'stripeCustomerId' => $user->stripe_customer_id,
            'current_country' => $user->current_country,
            'email' => $user->email ?? ($profile->email ?? null),
            'locale' => $profile->locale ?? (app()->getLocale() ?: 'en'),
            'currency' => MoneyPresenter::currency(null)['code'],
            'openTickets' => $openTickets,
            'favoriteOfficesCount' => $favoriteOffices,
        ];
    }

    private function photoUrl(?string $photo): ?string
    {
        if ($photo === null || $photo === '') {
            return null;
        }

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $photo;
        }

        return asset('storage/' . ltrim($photo, '/'));
    }
}
