<?php

namespace Tests\Feature\Fleet;

use App\Models\User;

/**
 * Rider saved places (routes/user.php → PlacesController).
 *
 *   GET    user/me/places
 *   POST   user/me/places        201
 *   PATCH  user/me/places/{id}   200
 *   DELETE user/me/places/{id}   204
 *
 * Contract details that drifted and are pinned here:
 *  - `label` is now REQUIRED and `title` is optional (SavePlaceRequest). The old
 *    "unknown labels collapse to `other`" whitelist is gone: whatever the rider
 *    types is stored verbatim, and an absent title falls back to the label.
 *  - update reuses the SAME SavePlaceRequest as store, so a PATCH is a full
 *    replacement — label/lat/lng must be resent or it is 422, never a partial
 *    merge. This is easy to mistake for a partial-update endpoint.
 *  - saved_places lives on the GLOBAL connection (SavedPlace::$connection), not
 *    the tenant shard.
 *
 * Ownership is the security boundary: every mutating handler resolves the row
 * via findForUser($id, $userId) and throws DomainException::notFound() — a
 * stranger gets 404, not 403, so the endpoint never confirms the row exists.
 */
class SavedPlacesTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    /** A saved place owned by $userId, returning its id. */
    private function place(int $userId, string $label = 'home', string $title = 'X'): int
    {
        return $this->asUser($userId)->postJson('user/me/places', [
            'label' => $label, 'title' => $title, 'lat' => 1, 'lng' => 2,
        ])->assertStatus(201)->json('data.id');
    }

    public function test_crud_flow(): void
    {
        $created = $this->asUser()->postJson('user/me/places', [
            'label' => 'home', 'title' => 'Al Sadd, Zone 38', 'lat' => 25.28, 'lng' => 51.53,
        ])->assertStatus(201)
            ->assertJsonPath('data.label', 'home')
            ->assertJsonPath('data.icon', 'pin')
            ->assertJsonPath('data.user_id', 7);

        $id = $created->json('data.id');

        $this->asUser()->getJson('user/me/places')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Al Sadd, Zone 38');

        // Update is a full replacement — label/lat/lng travel with every PATCH.
        $this->asUser()->patchJson("user/me/places/{$id}", [
            'label' => 'home', 'title' => 'Home Tower', 'lat' => 25.28, 'lng' => 51.53,
        ])->assertStatus(200)
            ->assertJsonPath('data.title', 'Home Tower')
            ->assertJsonPath('data.id', $id);

        $this->asUser()->deleteJson("user/me/places/{$id}")->assertStatus(204);
        $this->asUser()->getJson('user/me/places')->assertJsonPath('data', []);
    }

    public function test_store_requires_label(): void
    {
        $this->asUser()->postJson('user/me/places', ['lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /** Title is optional; when omitted the label doubles as the display title. */
    public function test_title_defaults_to_label(): void
    {
        $this->asUser()->postJson('user/me/places', ['label' => 'Work', 'lat' => 1, 'lng' => 2])
            ->assertStatus(201)
            ->assertJsonPath('data.label', 'Work')
            ->assertJsonPath('data.title', 'Work');
    }

    /**
     * There is no label whitelist any more — an arbitrary label survives the
     * round trip instead of collapsing to a canonical `other`.
     */
    public function test_unknown_label_is_stored_verbatim(): void
    {
        $this->asUser()->postJson('user/me/places', ['label' => 'gym', 'title' => 'X', 'lat' => 1, 'lng' => 2])
            ->assertStatus(201)
            ->assertJsonPath('data.label', 'gym');
    }

    /** A PATCH that drops the required fields is rejected, not partially applied. */
    public function test_partial_update_is_422(): void
    {
        $id = $this->place(7);

        $this->asUser(7)->patchJson("user/me/places/{$id}", ['title' => 'Home Tower'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── ownership scoping (the security boundary) ────────────────────────────

    public function test_foreign_place_is_404(): void
    {
        $id = $this->place(7);

        $this->asUser(8)->patchJson("user/me/places/{$id}", ['label' => 'hack', 'lat' => 1, 'lng' => 2])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        $this->asUser(8)->deleteJson("user/me/places/{$id}")->assertStatus(404);
    }

    /** …and a refused write really did not land. */
    public function test_foreign_update_does_not_mutate_the_row(): void
    {
        $id = $this->place(7, 'home', 'Al Sadd');

        $this->asUser(8)->patchJson("user/me/places/{$id}", ['label' => 'hack', 'title' => 'hack', 'lat' => 9, 'lng' => 9])
            ->assertStatus(404);

        $this->asUser(7)->getJson('user/me/places')
            ->assertJsonPath('data.0.label', 'home')
            ->assertJsonPath('data.0.title', 'Al Sadd');
    }

    /** The index is scoped too — user B never sees user A's places. */
    public function test_index_is_scoped_to_the_owner(): void
    {
        $this->place(7, 'home', 'Al Sadd');
        $this->place(8, 'work', 'West Bay');

        $this->asUser(7)->getJson('user/me/places')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Al Sadd');

        $this->asUser(8)->getJson('user/me/places')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'West Bay');
    }

    public function test_unknown_place_is_404(): void
    {
        $this->asUser(7)->deleteJson('user/me/places/424242')->assertStatus(404);
    }
}
