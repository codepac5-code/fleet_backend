<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Notification\LeadMailer;

class LeadMailerTest extends FleetTestCase
{
    public function test_noop_without_recipient_does_not_throw(): void
    {
        config(['mail.from.address' => null]);

        LeadMailer::notify('New lead', ['Name' => 'Sara', 'Phone' => null]);

        $this->assertTrue(true);
    }

    public function test_sends_via_array_transport_without_error(): void
    {
        config(['mail.from.address' => 'admin@fleet.test', 'mail.default' => 'array']);

        LeadMailer::notify('New office application: City Cabs', [
            'Office' => 'City Cabs',
            'Email' => 'a@b.com',
            'Phone' => null,
        ]);

        $this->assertTrue(true);
    }
}
