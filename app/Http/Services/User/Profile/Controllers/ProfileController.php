<?php

namespace App\Http\Services\User\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Account\ProfileService;
use App\Http\Core\Repositories\Account\RiderProfileRepository;
use App\Http\Services\User\Profile\Requests\NotificationPrefsRequest;
use App\Http\Services\User\Profile\Requests\PrivacyRequest;
use App\Http\Services\User\Profile\Requests\UpdateProfileRequest;
use App\Http\Services\User\Support\Presenters\UserPresenter;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    private const NOTIFICATION_DEFAULTS = [
        'tripUpdates' => true,
        'promotions' => true,
        'officeMessages' => true,
        'safetyAlerts' => true,
    ];

    private const PRIVACY_DEFAULTS = [
        'locationDuringTrips' => true,
        'shareTripDataWithOffice' => true,
        'marketing' => true,
    ];

    public function __construct(
        private ProfileService $profiles,
        private RiderProfileRepository $riderProfiles,
        private UserPresenter $presenter
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return Reply::ok($this->presenter->present($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if (array_key_exists('avatarUrl', $data)) {
            $user->photo = $data['avatarUrl'];
        }

        $attrs = [];

        if (array_key_exists('firstName', $data)) {
            $attrs['firstName'] = $data['firstName'];
        }

        if (array_key_exists('lastName', $data)) {
            $attrs['lastName'] = $data['lastName'];
        }

        if (array_key_exists('email', $data)) {
            $attrs['email'] = $data['email'];
        }

        if (array_key_exists('language', $data)) {
            $attrs['locale'] = $data['language'];
        }

        $this->profiles->update($user, $attrs);

        return Reply::ok($this->presenter->present($user));
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $path = $request->file('photo')->store('avatars', 'public');
        $user->photo = $path;
        $user->save();

        return Reply::ok($this->presenter->present($user));
    }

    public function destroy(Request $request): Response
    {
        $this->profiles->deleteAccount($request->user());

        return response()->noContent();
    }

    public function notificationPrefs(Request $request): JsonResponse
    {
        return Reply::ok($this->readPrefs($request, 'notification_prefs', self::NOTIFICATION_DEFAULTS));
    }

    public function updateNotificationPrefs(NotificationPrefsRequest $request): JsonResponse
    {
        return Reply::ok($this->writePrefs($request, 'notification_prefs', self::NOTIFICATION_DEFAULTS, $request->validated()));
    }

    public function privacy(Request $request): JsonResponse
    {
        return Reply::ok($this->readPrefs($request, 'privacy_prefs', self::PRIVACY_DEFAULTS));
    }

    public function updatePrivacy(PrivacyRequest $request): JsonResponse
    {
        return Reply::ok($this->writePrefs($request, 'privacy_prefs', self::PRIVACY_DEFAULTS, $request->validated()));
    }

    private function readPrefs(Request $request, string $column, array $defaults): array
    {
        $profile = $this->riderProfiles->forUser((int) $request->user()->id);

        return array_merge($defaults, (array) ($profile->{$column} ?? []));
    }

    private function writePrefs(Request $request, string $column, array $defaults, array $data): array
    {
        $profile = $this->riderProfiles->forUser((int) $request->user()->id);
        $merged = array_merge($defaults, (array) ($profile->{$column} ?? []), $data);
        $profile->{$column} = $merged;
        $this->riderProfiles->save($profile);

        return $merged;
    }
}
