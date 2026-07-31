<?php

namespace Tests\Feature\Fleet;

use App\Models\Complaint;
use App\Models\CorporateInvoice;
use App\Models\FamilyMember;
use App\Models\HelpSuggestion;
use App\Models\User;

class RiderV2SupportB2BTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
    ];

    protected array $tenantMigrations = [
        '2024_11_05_124211_create_admins_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        '2025_07_12_223226_create_help_suggestions_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_25_000005_add_country_to_support_tables.php',
        // Fleet-layer support tickets now publish a live alert to the admin room,
        // which lands in the transactional outbox.
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    public function test_open_list_and_show_ticket(): void
    {
        $ticketId = $this->asUser()->postJson('user/tickets', ['topic' => 'payment', 'message' => 'I was overcharged'])
            ->assertStatus(201)
            ->json('data.ticketId');

        $this->assertNotNull($ticketId);

        $this->asUser()->getJson('user/tickets')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $ticketId)
            ->assertJsonPath('data.0.topic', 'payment')
            ->assertJsonPath('data.0.messages.0.body', 'I was overcharged');

        $this->asUser()->getJson("user/tickets/{$ticketId}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $ticketId)
            ->assertJsonPath('data.messages.0.sender_type', 'user');

        $this->asUser(8)->getJson("user/tickets/{$ticketId}")->assertStatus(404);
    }

    public function test_reply_to_ticket_appends_a_user_message(): void
    {
        $ticketId = $this->asUser()->postJson('user/tickets', ['topic' => 'payment', 'message' => 'First message'])
            ->assertStatus(201)
            ->json('data.ticketId');

        $this->asUser()->postJson("user/tickets/{$ticketId}/messages", ['message' => 'Any update?'])
            ->assertStatus(201)
            ->assertJsonPath('data.id', $ticketId)
            ->assertJsonPath('data.messages.1.sender_type', 'user')
            ->assertJsonPath('data.messages.1.body', 'Any update?');

        // A blank reply is rejected, and a foreign user cannot reply.
        $this->asUser()->postJson("user/tickets/{$ticketId}/messages", ['message' => ''])->assertStatus(422);
        $this->asUser(8)->postJson("user/tickets/{$ticketId}/messages", ['message' => 'hi'])->assertStatus(404);
    }

    public function test_complaint_routing(): void
    {
        $this->asUser()->postJson('user/complaints', ['about' => 'driver', 'description' => 'Rude driver'])
            ->assertStatus(201)
            ->assertJsonPath('data.routed_to', 'office')
            ->assertJsonPath('data.priority', 'normal');

        $this->asUser()->postJson('user/complaints', ['about' => 'safety', 'description' => 'Felt unsafe'])
            ->assertStatus(201)
            ->assertJsonPath('data.routed_to', 'fleetos')
            ->assertJsonPath('data.priority', 'urgent');

        $this->assertSame(2, Complaint::query()->where('user_id', 7)->count());
    }

    public function test_help_articles(): void
    {
        HelpSuggestion::query()->create([
            'title' => 'How to cancel', 'title_en' => 'How to cancel', 'description' => 'Steps', 'description_en' => 'Steps',
            'isActive' => 1, 'priority' => 5, 'category' => 'rides', 'read_minutes' => 3, 'target_user' => 'user',
        ]);

        $id = $this->asUser()->getJson('user/help/articles?category=rides')
            ->assertStatus(200)
            ->assertJsonPath('data.0.title', 'How to cancel')
            ->assertJsonPath('data.0.readMinutes', 3)
            ->json('data.0.id');

        $this->asUser()->getJson("user/help/articles/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.read_minutes', 3)
            ->assertJsonPath('data.target_user', 'user');
    }

    public function test_corporate_invoices(): void
    {
        CorporateInvoice::query()->create([
            'user_id' => 7, 'month' => '2026-07', 'trips' => 12, 'amount_minor' => 45000, 'currency_code' => 'QAR', 'status' => 'unbilled',
        ]);
        CorporateInvoice::query()->create([
            'user_id' => 9, 'month' => '2026-07', 'trips' => 3, 'amount_minor' => 9000, 'currency_code' => 'QAR', 'status' => 'unbilled',
        ]);

        $this->asUser()->getJson('user/corporate/invoices')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.invoices')
            ->assertJsonPath('data.invoices.0.amount_minor', 45000)
            ->assertJsonPath('data.invoices.0.trips', 12);
    }

    public function test_family_members_crud(): void
    {
        $id = $this->asUser()->postJson('user/family/members', [
            'name' => 'Sara', 'phone' => '33009988', 'type' => 'minor', 'approvalRequired' => true, 'autoShare' => true,
        ])->assertStatus(201)
            ->assertJsonPath('data.name', 'Sara')
            ->assertJsonPath('data.approval_required', true)
            ->json('data.id');

        $this->asUser()->getJson('user/family/members')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Sara');

        $this->asUser()->patchJson("user/family/members/{$id}", ['name' => 'Sara Ali', 'autoShare' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Sara Ali')
            ->assertJsonPath('data.auto_share', false);

        $this->asUser()->deleteJson("user/family/members/{$id}")->assertStatus(204);
        $this->assertSame(0, FamilyMember::query()->where('user_id', 7)->count());

        $this->asUser(8)->patchJson("user/family/members/{$id}", ['name' => 'X'])->assertStatus(404);
    }
}
