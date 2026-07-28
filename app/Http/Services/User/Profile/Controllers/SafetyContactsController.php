<?php

namespace App\Http\Services\User\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Account\RiderProfileRepository;
use App\Http\Core\Repositories\Account\SafetyContactRepository;
use App\Http\Services\User\Profile\Requests\AutoShareRequest;
use App\Http\Services\User\Profile\Requests\SafetyContactRequest;
use App\Http\Services\User\Support\Presenters\ResourcePresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\SafetyContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SafetyContactsController extends Controller
{
    public function __construct(
        private SafetyContactRepository $contacts,
        private RiderProfileRepository $profiles
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $contacts = $this->contacts->listForUser($userId)
            ->map(fn (SafetyContact $c) => ResourcePresenter::safetyContact($c))
            ->all();

        return Reply::ok([
            'contacts' => $contacts,
            'autoShare' => $this->autoShare($userId),
        ]);
    }

    public function store(SafetyContactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = (int) $request->user()->id;
        $primary = (bool) ($data['primary'] ?? false);

        if ($primary) {
            SafetyContact::query()->where('user_id', $userId)->update(['is_primary' => false]);
        }

        $contact = $this->contacts->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'relation' => $data['relation'] ?? null,
            'is_primary' => $primary,
            'auto_share' => $this->autoShare($userId),
        ]);

        return Reply::ok(ResourcePresenter::safetyContact($contact), 201);
    }

    public function destroy(Request $request, int $id): Response
    {
        $contact = $this->contacts->findForUser($id, (int) $request->user()->id);

        if ($contact === null) {
            throw DomainException::notFound();
        }

        $this->contacts->delete($contact);

        return response()->noContent();
    }

    public function autoShareToggle(AutoShareRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = (int) $request->user()->id;

        $profile = $this->profiles->forUser($userId);
        $profile->auto_share_safety = $data['enabled'];
        $this->profiles->save($profile);

        SafetyContact::query()->where('user_id', $userId)->update(['auto_share' => $data['enabled']]);

        return Reply::ok(['enabled' => (bool) $data['enabled']]);
    }

    private function autoShare(int $userId): bool
    {
        $profile = $this->profiles->forUser($userId);

        return $profile->auto_share_safety === null ? true : (bool) $profile->auto_share_safety;
    }
}
