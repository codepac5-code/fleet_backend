<?php

namespace Tests\Feature\Fleet;

use App\Http\Controllers\ContactMessageController;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactMessageTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_01_000001_create_contact_messages_table.php',
    ];

    public function test_valid_contact_message_is_stored(): void
    {
        $request = Request::create('/contact-message', 'POST', [
            'intent' => 'demo',
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'phone' => '+974123',
            'company' => 'City Cabs',
            'message' => 'Please show me a demo.',
        ]);

        $response = (new ContactMessageController())->store($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, ContactMessage::query()->where('email', 'sara@example.com')->where('status', 'new')->count());
    }

    public function test_invalid_intent_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/contact-message', 'POST', [
            'intent' => 'spam',
            'name' => 'X',
            'email' => 'x@example.com',
        ]);

        (new ContactMessageController())->store($request);
    }

    public function test_missing_required_fields_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/contact-message', 'POST', ['intent' => 'demo']);

        (new ContactMessageController())->store($request);
    }
}
